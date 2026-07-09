USE monitoring_cr;

SET @rspad_client_id = (SELECT id FROM clients WHERE code = 'RSPAD' LIMIT 1);

INSERT INTO cr_items
(id, client_id, display_order, form_name, sub_area, cr_title, stage, ticket_no, pic, blocker, blocker_type, target_date, target_label, today_update, last_update_at, is_hold_contract, is_rework, need_clarification, need_decision, is_priority)
VALUES
(1, @rspad_client_id, 1, 'Informed Consent', NULL, 'Ubah label "Pasien" -> "Pasien/Wali"', 'QC/UAT', 'RSPAD-IC-001', 'Morris', '-', 'none', '2026-07-12', NULL, 'masuk QC', '2026-07-08', 0, 0, 0, 0, 0),
(2, @rspad_client_id, 2, 'Informed Consent', NULL, 'Tambah kolom saksi kedua', 'Development', 'RSPAD-IC-002', 'Febio', 'Dev', 'internal', '2026-07-06', NULL, 'dalam pengerjaan dev', '2026-07-08', 0, 0, 0, 0, 0),
(3, @rspad_client_id, 3, 'Flowsheet ICU', 'TTV', 'Grafik TTV time-axis + pan/zoom', 'Development', 'RSPAD-ICU-TTV', 'Febio', 'Dev', 'internal', '2026-07-20', NULL, 'integrasi grafik TTV berjalan', '2026-07-08', 0, 0, 0, 0, 1),
(4, @rspad_client_id, 4, 'Flowsheet ICU', 'SSP & OBS', 'Observasi SSP dan OBS PRD + notulen', 'Development', 'GS02-26074174', 'Morris', 'RS/User', 'external', '2026-07-15', 'next menunggu user ttd Notulen', 'SSP dan OBS PRD selesai dan telah terbit tiket. Menunggu TTD notulen SSP OBS.', '2026-07-08', 0, 0, 1, 1, 0),
(5, @rspad_client_id, 5, 'Flowsheet ICU', 'Respiratory', 'Form respiratory dan ventilator ICU', 'Development', 'RSPAD-ICU-RESP', 'Morris', 'RS/User', 'external', '2026-07-15', NULL, 'menunggu konfirmasi notulen respiratory dari user', '2026-07-08', 0, 0, 1, 1, 0),
(6, @rspad_client_id, 6, 'Flowsheet ICU', 'Hemodinamik', 'Parameter hemodinamik ICU', 'Scope Fix', NULL, 'Febio', '-', 'none', '2026-07-18', NULL, 'scope parameter hemodinamik dikunci', '2026-07-08', 0, 0, 0, 0, 0),
(7, @rspad_client_id, 7, 'Flowsheet ICU', 'Balance Cairan', 'Balance cairan intake-output', 'Development', 'RSPAD-ICU-BAL', 'Febio', 'Dev', 'internal', '2026-07-19', NULL, 'mapping intake-output berjalan', '2026-07-08', 0, 0, 0, 0, 0),
(8, @rspad_client_id, 8, 'Flowsheet ICU', 'Hasil Penunjang', 'Ringkasan hasil penunjang ICU', 'Scope Fix', NULL, 'Morris', '-', 'none', '2026-07-18', NULL, 'scope hasil penunjang dikunci dengan user', '2026-07-08', 0, 0, 0, 0, 0),
(9, @rspad_client_id, 9, 'Flowsheet ICU', 'Petunjuk', 'Petunjuk pengisian flowsheet ICU', 'Scope Fix', NULL, 'Morris', '-', 'none', '2026-07-18', NULL, 'draft petunjuk pengisian disiapkan', '2026-07-08', 0, 0, 0, 0, 0),
(10, @rspad_client_id, 10, 'Flowsheet ICU', 'CPPT', 'Integrasi CPPT ICU', 'Development', 'RSPAD-ICU-CPPT', 'Akbar', 'Desain', 'internal', '2026-07-17', NULL, 'menunggu finalisasi desain CPPT ICU', '2026-07-08', 0, 0, 0, 0, 0),
(11, @rspad_client_id, 11, 'Flowsheet ICU', 'Diagnosa Keperawatan ICU', 'Diagnosa keperawatan ICU', 'Klarifikasi', NULL, 'Indra', 'RS/User', 'external', '2026-07-10', NULL, 'menunggu daftar diagnosa final dari user', '2026-07-08', 0, 0, 1, 0, 0),
(12, @rspad_client_id, 12, 'Edukasi Pasien', NULL, 'Form edukasi terintegrasi per DPJP', 'QC/UAT', 'RSPAD-EDU-003', 'Morris', 'QC', 'internal', '2026-07-11', NULL, 'QC internal berjalan', '2026-07-08', 0, 0, 0, 0, 0),
(13, @rspad_client_id, 13, 'Cardex', NULL, 'Cetak kardeks per shift perawat', 'Ready', 'RSPAD-CDX-007', 'Heri', 'Kontrak', 'external', NULL, 'Selesai', 'selesai, menunggu administrasi kontrak', '2026-07-08', 1, 0, 0, 0, 0),
(14, @rspad_client_id, 14, 'Pengajuan Pembedahan', NULL, 'Alur approval SpB sebelum jadwal OK', 'Klarifikasi', NULL, 'Andreas', 'RS/User', 'external', '2026-07-04', NULL, 'menunggu konfirmasi alur approval dari RS', '2026-07-06', 0, 0, 1, 0, 0),
(15, @rspad_client_id, 15, 'Perioperatif', NULL, 'Checklist pre-op sesuai form RS', 'Development', 'RSPAD-PRP-009', 'Febio', 'Dev', 'internal', '2026-07-18', NULL, 'checklist dalam pengerjaan', '2026-07-08', 0, 0, 0, 0, 0),
(16, @rspad_client_id, 16, 'SBAR / Pemindahan Pasien', NULL, 'Form SBAR transfer antar ruang', 'Scope Fix', NULL, 'Indra', '-', 'none', '2026-07-16', NULL, 'scope dikunci dengan user', '2026-07-08', 0, 0, 0, 0, 0),
(17, @rspad_client_id, 17, 'Discharge Planning', NULL, 'Rencana pulang H-1 + edukasi pulang', 'Development', 'RSPAD-DIS-011', 'Akbar', 'Desain', 'internal', '2026-07-14', NULL, 'menunggu finalisasi desain (Figma)', '2026-07-08', 0, 0, 0, 0, 0),
(18, @rspad_client_id, 18, 'SSC Anestesi', NULL, 'Redaksi cetakan disesuaikan form RS', 'Development', 'RSPAD-SSC-006', 'Andreas', 'RS/User', 'external', '2026-07-05', NULL, 'menunggu contoh form cetak dari RS', '2026-07-07', 0, 1, 0, 0, 0),
(19, @rspad_client_id, 19, 'SKL (Ket. Lahir)', NULL, 'Generate otomatis dari data ibu', 'QC/UAT', 'RSPAD-SKL-012', 'Morris', 'QC', 'internal', '2026-07-13', NULL, 'QC internal berjalan', '2026-07-08', 0, 0, 0, 0, 0),
(20, @rspad_client_id, 20, 'Sertifikat Kematian', NULL, 'Trigger field kelompok penyebab kematian', 'Klarifikasi', NULL, 'Andreas', 'RS/User', 'external', '2026-07-06', NULL, 'menunggu konfirmasi trigger dari user', '2026-07-06', 0, 0, 1, 0, 1),
(21, @rspad_client_id, 21, 'Catatan Anestesi', NULL, 'Observasi anestesi masuk release-1 / PDF dulu?', 'Klarifikasi', NULL, 'Indra', 'RS/User', 'external', '2026-07-07', NULL, 'menunggu keputusan RS: release-1 atau PDF manual', '2026-07-08', 0, 0, 0, 1, 0);

INSERT INTO cr_events
(cr_item_id, event_type, field_name, old_value, new_value, note, created_by, created_at)
VALUES
(1, 'stage', 'stage', 'Development', 'QC/UAT', 'label Pasien/Wali masuk QC', 'admin', CONCAT(CURDATE(), ' 09:10:00')),
(12, 'field_change', 'today_update', NULL, 'QC internal berjalan', 'QC internal berjalan', 'admin', CONCAT(CURDATE(), ' 10:20:00')),
(19, 'field_change', 'today_update', NULL, 'QC internal berjalan', 'QC internal berjalan', 'admin', CONCAT(CURDATE(), ' 11:05:00')),
(3, 'field_change', 'today_update', NULL, 'integrasi grafik TTV berjalan', 'integrasi grafik TTV berjalan', 'dev', CONCAT(CURDATE(), ' 13:40:00')),
(4, 'field_change', 'today_update', NULL, 'SSP dan OBS PRD selesai dan telah terbit tiket', 'SSP dan OBS PRD selesai dan telah terbit tiket', 'admin', CONCAT(CURDATE(), ' 14:10:00')),
(13, 'stage', 'stage', 'QC/UAT', 'Ready', 'selesai, siap release, menunggu kontrak', 'admin', CONCAT(CURDATE(), ' 15:15:00'));
