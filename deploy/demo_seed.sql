-- Akun & data demo (aplikasi ini menyimpan password plaintext, sesuai kode asli)
-- Admin bawaan rbti.sql: Admin / 1234
USE rbti;
INSERT INTO users (id, name, email, password, nim, status) VALUES
(1, 'Mahasiswa Demo', 'mahasiswa@demo.local', 'demo123', '21070000000001', 'approved'),
(2, 'Pendaftar Baru', 'pendaftar@demo.local', 'demo123', '21070000000002', 'pending');
INSERT INTO Book (bookID, title, author, genre, totalCopies, availableCopies, status) VALUES
(1, 'Pengantar Teknik Industri', 'Demo Author A', 'Teknik Industri', 5, 4, 'available'),
(2, 'Riset Operasi', 'Demo Author B', 'Optimasi', 4, 3, 'available'),
(3, 'Sistem Informasi Manajemen', 'Demo Author C', 'Sistem Informasi', 3, 3, 'available'),
(4, 'Statistika Industri', 'Demo Author D', 'Statistika', 6, 6, 'available'),
(5, 'Simulasi Sistem Diskrit', 'Demo Author E', 'Simulasi', 2, 0, 'unavailable'),
(6, 'Ergonomi dan Perancangan Kerja', 'Demo Author F', 'Ergonomi', 3, 3, 'available'),
(7, 'Pengendalian Kualitas', 'Demo Author G', 'Kualitas', 4, 4, 'available'),
(8, 'Manajemen Rantai Pasok', 'Demo Author H', 'Supply Chain', 5, 5, 'available');
INSERT INTO Borrow (userID, bookID, borrowDate, returnDate, status) VALUES
(1, 1, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 9 DAY, 'borrowed'),
(1, 2, CURDATE(), CURDATE() + INTERVAL 14 DAY, 'borrow pending'),
(1, 5, CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 6 DAY, 'returned');
INSERT INTO Notification (userID, message, status) VALUES (1, 'Peminjaman "Pengantar Teknik Industri" disetujui.', 'unread');
