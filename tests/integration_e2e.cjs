const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const BASE = process.env.RBTI_BASE_URL || 'http://127.0.0.1:8088';
const LOG_FILE = path.resolve(process.cwd(), 'logs', 'mail_outbox.log');

const results = [];
function record(name, passed, detail = '') {
  results.push({ name, passed, detail });
  const icon = passed ? 'PASS' : 'FAIL';
  console.log(`[${icon}] ${name}${detail ? ' — ' + detail : ''}`);
}

async function runTest(name, fn) {
  try {
    const detail = await fn();
    record(name, true, detail || '');
  } catch (err) {
    record(name, false, err.message);
  }
}

async function createSession(browser) {
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  return ctx;
}

async function main() {
  const browser = await chromium.launch();

  try {
    // 1. Unauthenticated RBAC checks
    await runTest('RBAC: Unauthenticated admin page redirects to #admin-section', async () => {
      const ctx = await createSession(browser);
      const page = await ctx.newPage();
      await page.goto(`${BASE}/admin_dashboard.php`, { waitUntil: 'networkidle' });
      assert.ok(page.url().includes('register.php'), `Expected redirect to register.php, got ${page.url()}`);
      await ctx.close();
      return page.url();
    });

    await runTest('RBAC: Unauthenticated admin API endpoints return HTTP 403', async () => {
      const ctx = await createSession(browser);
      for (const ep of ['add_books.php', 'edit_book.php', 'delete_book.php', 'proses-pinjam.php']) {
        const res = await ctx.request.post(`${BASE}/${ep}`, { form: { id: '1', borrowID: '1', action: 'borrowing' } });
        assert.equal(res.status(), 403, `${ep} should return 403, got ${res.status()}`);
      }
      await ctx.close();
      return 'All 4 admin endpoints returned 403';
    });

    await runTest('RBAC: Unauthenticated member page redirects to login', async () => {
      const ctx = await createSession(browser);
      const page = await ctx.newPage();
      await page.goto(`${BASE}/index.php`, { waitUntil: 'networkidle' });
      assert.ok(page.url().includes('register.php'), `Expected redirect to register.php, got ${page.url()}`);
      await ctx.close();
    });

    // 2. Visitor login & live search
    await runTest('Visitor: Login and search live MariaDB books', async () => {
      const ctx = await createSession(browser);
      const page = await ctx.newPage();
      await page.goto(`${BASE}/register.php`, { waitUntil: 'networkidle' });
      await page.fill('#visitor-name', 'Pengunjung E2E');
      await page.fill('#visitor-email', 'visitor.e2e@example.com');
      await page.click('#visitor-form button[type="submit"]');
      await page.waitForURL('**/index-visitor.php');

      await page.goto(`${BASE}/pencarian-buku-visitor.php`, { waitUntil: 'networkidle' });
      await page.waitForSelector('#booksTable tbody tr');
      const text = await page.textContent('#booksTable tbody');
      assert.ok(text.includes('Pengantar Teknik Industri'), 'Expected seeded book in visitor search');
      await ctx.close();
      return 'Visitor login + live OPAC search verified';
    });

    // 3. Registration, pending block, admin login, approve & reject
    const uniqueSuffix = Date.now().toString().slice(-6);
    const nim1 = `210799${uniqueSuffix}1`;
    const nim2 = `210799${uniqueSuffix}2`;
    const email1 = `e2e1.${uniqueSuffix}@students.undip.ac.id`;
    const email2 = `e2e2.${uniqueSuffix}@students.undip.ac.id`;

    await runTest('Auth: Register two new members and block login while pending', async () => {
      const ctx = await createSession(browser);
      for (const [name, nim, email] of [
        ['E2E Member One', nim1, email1],
        ['E2E Member Two', nim2, email2],
      ]) {
        const res = await ctx.request.post(`${BASE}/register-process.php`, {
          form: { name, nim, email, password: 'Password123!' },
        });
        assert.ok(res.ok());
      }

      // Attempt login as pending member
      const page = await ctx.newPage();
      await page.goto(`${BASE}/register.php#member-section`, { waitUntil: 'networkidle' });
      await page.fill('#member-nim', nim1);
      await page.fill('#member-password', 'Password123!');
      await page.click('#member-form button[type="submit"]');
      await page.waitForLoadState('networkidle');
      assert.ok(page.url().includes('register.php'), 'Pending member must not enter index.php');
      const bodyText = await page.textContent('body');
      assert.ok(bodyText.includes('menunggu persetujuan'), 'Expected pending approval alert');
      await ctx.close();
      return `Registered ${nim1} & ${nim2}; pending login blocked`;
    });

    // Keep persistent Admin session
    const adminCtx = await createSession(browser);
    const adminPage = await adminCtx.newPage();

    await runTest('Auth: Admin login and live dashboard KPIs', async () => {
      await adminPage.goto(`${BASE}/register.php#admin-section`, { waitUntil: 'networkidle' });
      await adminPage.fill('#admin-username', 'Admin');
      await adminPage.fill('#admin-password', '1234');
      await adminPage.click('#admin-form button[type="submit"]');
      await adminPage.waitForURL('**/admin_dashboard.php');
      const html = await adminPage.textContent('body');
      assert.ok(html.includes('Total Judul Buku'), 'Expected live admin dashboard KPIs');
      return 'Admin logged in and dashboard loaded';
    });

    let member1Id = 0;
    await runTest('Admin: Approve pending member 1 and reject pending member 2', async () => {
      const listRes = await adminCtx.request.get(`${BASE}/approve_member.php?action=fetch`);
      assert.equal(listRes.status(), 200);
      const members = await listRes.json();
      const m1 = members.find(m => m.nim === nim1);
      const m2 = members.find(m => m.nim === nim2);
      assert.ok(m1 && m2, 'Both newly registered members should be listed');
      member1Id = m1.id;

      const appRes = await adminCtx.request.post(`${BASE}/approve_member.php`, {
        form: { 'approve_ids[]': String(m1.id) },
      });
      assert.equal(appRes.status(), 200);

      const rejRes = await adminCtx.request.post(`${BASE}/approve_member.php`, {
        form: { reject_id: String(m2.id) },
      });
      assert.equal(rejRes.status(), 200);

      const after = await (await adminCtx.request.get(`${BASE}/approve_member.php?action=fetch`)).json();
      assert.equal(after.find(m => m.nim === nim1)?.status, 'approved');
      assert.equal(after.find(m => m.nim === nim2), undefined);
      return `Approved ID=${m1.id}, rejected ID=${m2.id}`;
    });

    // 4. Book CRUD + CSV Upload + Invalid Input + FK Delete Protection
    let testBookId = 0;
    let limitedBookId = 0;
    await runTest('Books: Create, invalid-input check, edit, CSV upload, and FK delete protection', async () => {
      // Invalid input (available > total)
      const badRes = await adminCtx.request.post(`${BASE}/add_books.php`, {
        form: { title: 'Bad Book', author: 'X', genre: 'Sirkulasi', totalCopies: '2', availableCopies: '5' },
      });
      assert.equal(badRes.status(), 400, 'Should reject availableCopies > totalCopies');

      // Create valid test book (2 copies)
      const addRes = await adminCtx.request.post(`${BASE}/add_books.php`, {
        form: { title: `Buku Uji E2E ${uniqueSuffix}`, author: 'QA Author', genre: 'Sirkulasi', totalCopies: '2', availableCopies: '2' },
      });
      assert.equal(await addRes.text(), 'success');

      // Create limited 1-copy book for concurrency test
      const addLim = await adminCtx.request.post(`${BASE}/add_books.php`, {
        form: { title: `Buku Terbatas ${uniqueSuffix}`, author: 'QA Author', genre: 'Sirkulasi', totalCopies: '1', availableCopies: '1' },
      });
      assert.equal(await addLim.text(), 'success');

      // Create disposable book for delete test
      await adminCtx.request.post(`${BASE}/add_books.php`, {
        form: { title: `Buku Hapus ${uniqueSuffix}`, author: 'Temp', genre: 'Sirkulasi', totalCopies: '1', availableCopies: '1' },
      });

      const books = await (await adminCtx.request.get(`${BASE}/get_books.php`)).json();
      const bTest = books.find(b => b.title === `Buku Uji E2E ${uniqueSuffix}`);
      const bLim = books.find(b => b.title === `Buku Terbatas ${uniqueSuffix}`);
      const bDel = books.find(b => b.title === `Buku Hapus ${uniqueSuffix}`);
      assert.ok(bTest && bLim && bDel, 'Created books must persist in MariaDB');
      testBookId = bTest.bookID;
      limitedBookId = bLim.bookID;

      // Edit book
      const editRes = await adminCtx.request.post(`${BASE}/edit_book.php`, {
        form: { id: String(testBookId), title: `Buku Uji E2E ${uniqueSuffix}`, author: 'QA Author Updated', genre: 'Sirkulasi', totalCopies: '2', availableCopies: '2' },
      });
      assert.equal(await editRes.text(), 'success');

      // Delete disposable book (no borrows) -> succeeds
      const delOk = await adminCtx.request.post(`${BASE}/delete_book.php`, { form: { id: String(bDel.bookID) } });
      assert.equal(await delOk.text(), 'success');

      // Delete book with borrow history (ID=1 from demo seed) -> HTTP 409
      const delFk = await adminCtx.request.post(`${BASE}/delete_book.php`, { form: { id: '1' } });
      assert.equal(delFk.status(), 409, 'Deleting book with borrow history must return 409');

      // Upload valid CSV
      const csvContent = `title,author,genre,totalCopies,availableCopies\nCSV Book ${uniqueSuffix},CSV Author,Sirkulasi,3,3\n`;
      const csvRes = await adminCtx.request.post(`${BASE}/upload_spreadsheet.php`, {
        multipart: {
          spreadsheet: { name: 'books.csv', mimeType: 'text/csv', buffer: Buffer.from(csvContent, 'utf-8') },
        },
      });
      assert.equal(await csvRes.text(), 'success');

      // Upload invalid file type (.txt) -> HTTP 400
      const badCsv = await adminCtx.request.post(`${BASE}/upload_spreadsheet.php`, {
        multipart: {
          spreadsheet: { name: 'bad.txt', mimeType: 'text/plain', buffer: Buffer.from('hello', 'utf-8') },
        },
      });
      assert.equal(badCsv.status(), 400);

      return `CRUD + CSV + FK protection passed (testBookId=${testBookId}, limitedBookId=${limitedBookId})`;
    });

    // 5. Login as approved Member 1 + RBAC check against Admin endpoints
    const member1Ctx = await createSession(browser);
    const member1Page = await member1Ctx.newPage();

    await runTest('Auth & RBAC: Approved member logs in and is blocked from admin routes', async () => {
      await member1Page.goto(`${BASE}/register.php#member-section`, { waitUntil: 'networkidle' });
      await member1Page.fill('#member-nim', nim1);
      await member1Page.fill('#member-password', 'Password123!');
      await member1Page.click('#member-form button[type="submit"]');
      await member1Page.waitForURL('**/index.php');

      // Member must be blocked from admin API
      const forbiddenRes = await member1Ctx.request.post(`${BASE}/delete_book.php`, { form: { id: String(testBookId) } });
      assert.equal(forbiddenRes.status(), 403, 'Member must get 403 on admin API');
      return 'Member login succeeded & admin API returned 403';
    });

    // 6. Full Borrowing, Duplicate Guard, Extension (Approve, Reject, Max-2 Limit), Return & Stock Integrity
    let borrowId = 0;
    await runTest('Circulation: Borrow, duplicate guard, atomic approval, no double-decrement', async () => {
      // Member requests borrow
      const req1 = await (await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'borrow', bookIdentifier: String(testBookId) },
      })).json();
      assert.equal(req1.success, true);

      // Stock must NOT decrement before admin approval
      let books = await (await adminCtx.request.get(`${BASE}/get_books.php`)).json();
      assert.equal(books.find(b => b.bookID === testBookId).availableCopies, 2, 'Stock must remain 2 while pending');

      // Duplicate borrow request by same member -> rejected
      const dup = await (await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'borrow', bookIdentifier: String(testBookId) },
      })).json();
      assert.equal(dup.success, false, 'Duplicate borrow request must be rejected');

      // Find borrowID
      const borrows = await (await adminCtx.request.get(`${BASE}/proses-pinjam.php?action=fetch`)).json();
      const row = borrows.find(b => Number(b.bookID) === Number(testBookId) && Number(b.userID) === Number(member1Id));
      assert.ok(row, 'Pending borrow row must exist');
      borrowId = row.borrowID;

      // Admin approves borrowing
      const app1 = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'borrowing', borrowID: String(borrowId) },
      })).json();
      assert.equal(app1.success, true);

      // Repeated approval must fail and NOT decrement again
      const app2 = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'borrowing', borrowID: String(borrowId) },
      })).json();
      assert.equal(app2.success, false, 'Repeated approval must fail');

      books = await (await adminCtx.request.get(`${BASE}/get_books.php`)).json();
      assert.equal(books.find(b => b.bookID === testBookId).availableCopies, 1, 'Stock must be exactly 1 after approval');

      return `borrowID=${borrowId} approved once; stock 2 -> 1`;
    });

    await runTest('Circulation: Extension approve, reject, max-2 limit, and return stock restoration', async () => {
      // Extension #1: request & approve
      const e1 = await (await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'extend', borrowID: String(borrowId) },
      })).json();
      assert.equal(e1.success, true);

      const ea1 = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'extending', borrowID: String(borrowId) },
      })).json();
      assert.equal(ea1.success, true);

      // Repeated extension approval must fail
      const ea1Dup = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'extending', borrowID: String(borrowId) },
      })).json();
      assert.equal(ea1Dup.success, false);

      // Extension #2: request & REJECT
      await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'extend', borrowID: String(borrowId) },
      });
      const er = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'reject_extending', borrowID: String(borrowId) },
      })).json();
      assert.equal(er.success, true);

      // Extension #2 again: request & approve (extend_count reaches 2)
      await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'extend', borrowID: String(borrowId) },
      });
      const ea2 = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'extending', borrowID: String(borrowId) },
      })).json();
      assert.equal(ea2.success, true);

      // Extension #3: must be blocked by max-2 limit
      const e3 = await (await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'extend', borrowID: String(borrowId) },
      })).json();
      assert.equal(e3.success, false, '3rd extension must be rejected');

      // Member requests return -> Admin confirms -> Repeated confirm fails -> Stock restored to 2
      const retReq = await (await member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, {
        form: { action: 'return', borrowID: String(borrowId) },
      })).json();
      assert.equal(retReq.success, true);

      const retConf = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'returning', borrowID: String(borrowId) },
      })).json();
      assert.equal(retConf.success, true);

      const retConfDup = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'returning', borrowID: String(borrowId) },
      })).json();
      assert.equal(retConfDup.success, false, 'Repeated return confirm must fail');

      const books = await (await adminCtx.request.get(`${BASE}/get_books.php`)).json();
      assert.equal(books.find(b => b.bookID === testBookId).availableCopies, 2, 'Stock must be restored to 2');

      // Verify notifications appeared on member dashboard
      await member1Page.goto(`${BASE}/index.php`, { waitUntil: 'networkidle' });
      const dashText = await member1Page.textContent('body');
      assert.ok(dashText.includes('Notifikasi Terbaru') && dashText.includes('Pengembalian buku'), 'Expected notification on member dashboard');

      return 'Extensions (approve/reject/max-2) + Return + Notifications verified';
    });

    // 7. CONCURRENCY / RACE CONDITION TEST: 2 members competing for 1 last copy
    const demoMemberCtx = await createSession(browser);
    await runTest('Concurrency: Simultaneous admin approvals for 1-copy book allow exactly ONE', async () => {
      // Login as Mahasiswa Demo (21070000000001 / demo123)
      const p = await demoMemberCtx.newPage();
      await p.goto(`${BASE}/register.php#member-section`, { waitUntil: 'networkidle' });
      await p.fill('#member-nim', '21070000000001');
      await p.fill('#member-password', 'demo123');
      await p.click('#member-form button[type="submit"]');
      await p.waitForURL('**/index.php');

      // Both members submit borrow pending for limitedBookId (availableCopies = 1)
      const [rA, rB] = await Promise.all([
        member1Ctx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, { form: { action: 'borrow', bookIdentifier: String(limitedBookId) } }),
        demoMemberCtx.request.post(`${BASE}/peminjaman_buku_pengguna.php`, { form: { action: 'borrow', bookIdentifier: String(limitedBookId) } }),
      ]);
      assert.equal((await rA.json()).success, true);
      assert.equal((await rB.json()).success, true);

      const borrows = await (await adminCtx.request.get(`${BASE}/proses-pinjam.php?action=fetch`)).json();
      const pending = borrows.filter(b => Number(b.bookID) === Number(limitedBookId) && b.status === 'borrow pending');
      assert.equal(pending.length, 2, 'Expected 2 pending requests for the 1-copy book');

      // Fire 2 concurrent admin approvals simultaneously
      const [c1, c2] = await Promise.all([
        adminCtx.request.post(`${BASE}/proses-pinjam.php`, { form: { action: 'borrowing', borrowID: String(pending[0].borrowID) } }).then(r => r.json()),
        adminCtx.request.post(`${BASE}/proses-pinjam.php`, { form: { action: 'borrowing', borrowID: String(pending[1].borrowID) } }).then(r => r.json()),
      ]);

      const successes = [c1.success, c2.success].filter(Boolean).length;
      assert.equal(successes, 1, `Exactly 1 concurrent approval must succeed, got ${successes} (${JSON.stringify([c1, c2])})`);

      const books = await (await adminCtx.request.get(`${BASE}/get_books.php`)).json();
      const limBook = books.find(b => b.bookID === limitedBookId);
      assert.equal(limBook.availableCopies, 0, 'availableCopies must be 0, never negative');
      assert.equal(limBook.status, 'unavailable', 'status must be unavailable');

      // Reject the losing pending request cleanly
      const loserId = c1.success ? pending[1].borrowID : pending[0].borrowID;
      const rejRes = await (await adminCtx.request.post(`${BASE}/proses-pinjam.php`, {
        form: { action: 'reject_borrowing', borrowID: String(loserId) },
      })).json();
      assert.equal(rejRes.success, true);

      return 'Row-level locking prevented overselling; 1 approved, 1 blocked, stock=0';
    });

    // 8. Password Recovery End-to-End (non-enumerating, single-use, invalid token, login with new password)
    await runTest('Password Recovery: Non-enumerating submit, single-use reset, and login with new password', async () => {
      const ctx = await createSession(browser);
      const page = await ctx.newPage();

      // Non-existent account
      await page.goto(`${BASE}/forgot-password.php`, { waitUntil: 'networkidle' });
      await page.fill('#identifier', 'nonexistent_999999');
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');
      const msgUnknown = await page.textContent('.rbti-auth-alert--success');

      // Existing account (member 1)
      await page.goto(`${BASE}/forgot-password.php`, { waitUntil: 'networkidle' });
      await page.fill('#identifier', nim1);
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');
      const msgKnown = await page.textContent('.rbti-auth-alert--success');

      assert.equal(msgUnknown.trim(), msgKnown.trim(), 'Forgot-password response must be identical (non-enumerating)');

      // Extract real reset link from local dev outbox log
      assert.ok(fs.existsSync(LOG_FILE), 'logs/mail_outbox.log must exist');
      const logLines = fs.readFileSync(LOG_FILE, 'utf-8').trim().split(/\r?\n/);
      const matchLine = [...logLines].reverse().find(l => l.includes(email1));
      assert.ok(matchLine, 'Reset link for member 1 must be recorded in local mail outbox');
      const tokenMatch = matchLine.match(/token=([a-f0-9]+)/i);
      assert.ok(tokenMatch, 'Valid hex token must be present');
      const token = tokenMatch[1];

      // Invalid token check
      await page.goto(`${BASE}/reset-password.php?token=invalidtoken000`, { waitUntil: 'networkidle' });
      assert.ok((await page.textContent('body')).includes('tidak valid'), 'Invalid token must show error');

      // Valid token password reset
      await page.goto(`${BASE}/reset-password.php?token=${token}`, { waitUntil: 'networkidle' });
      await page.fill('#new-password', 'ResetPass999!');
      await page.fill('#confirm-password', 'ResetPass999!');
      await page.click('button[type="submit"]');
      await page.waitForURL('**/register.php*');
      assert.ok((await page.textContent('body')).includes('berhasil diperbarui'));

      // Single-use enforcement: reusing token must fail
      await page.goto(`${BASE}/reset-password.php?token=${token}`, { waitUntil: 'networkidle' });
      assert.ok((await page.textContent('body')).includes('tidak valid'), 'Reused token must be rejected');

      // Login with new password
      await page.goto(`${BASE}/register.php#member-section`, { waitUntil: 'networkidle' });
      await page.fill('#member-nim', nim1);
      await page.fill('#member-password', 'ResetPass999!');
      await page.click('#member-form button[type="submit"]');
      await page.waitForURL('**/index.php');

      await ctx.close();
      return 'Non-enumerating + single-use token + login with new password verified';
    });

    await adminCtx.close();
    await member1Ctx.close();
    await demoMemberCtx.close();
  } finally {
    await browser.close();
  }

  const passed = results.filter(r => r.passed).length;
  const failed = results.filter(r => !r.passed).length;
  console.log(`\nSummary: ${passed}/${results.length} passed, ${failed} failed.`);
  if (failed > 0) {
    process.exit(1);
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
