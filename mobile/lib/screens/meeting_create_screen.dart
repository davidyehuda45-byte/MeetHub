import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class MeetingCreateScreen extends StatefulWidget {
  const MeetingCreateScreen({super.key});

  @override
  State<MeetingCreateScreen> createState() => _MeetingCreateScreenState();
}

class _MeetingCreateScreenState extends State<MeetingCreateScreen> {
  final _titleCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  final _pwdCtrl = TextEditingController();

  DateTime _selectedDate = DateTime.now();
  TimeOfDay _selectedTime = TimeOfDay.now();
  int _durationMinutes = 60;
  bool _allowGuests = true;
  bool _lobbyEnabled = false;
  bool _startMuted = false;
  bool _isLoading = false;

  Future<void> _submit() async {
    final title = _titleCtrl.text.trim();
    if (title.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Judul rapat wajib diisi.')),
      );
      return;
    }

    setState(() => _isLoading = true);

    final scheduledAt = DateTime(
      _selectedDate.year,
      _selectedDate.month,
      _selectedDate.day,
      _selectedTime.hour,
      _selectedTime.minute,
    );

    final res = await ApiService().createScheduledMeeting({
      'title': title,
      'description': _descCtrl.text.trim().isEmpty ? null : _descCtrl.text.trim(),
      'scheduled_at': scheduledAt.toIso8601String(),
      'duration_minutes': _durationMinutes,
      'password': _pwdCtrl.text.trim().isEmpty ? null : _pwdCtrl.text.trim(),
      'allow_guests': _allowGuests,
      'lobby_enabled': _lobbyEnabled,
      'start_muted': _startMuted,
    });

    setState(() => _isLoading = false);

    if (res != null) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Rapat '${res.title}' berhasil dibuat!")),
      );
      Navigator.pop(context, true);
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal membuat jadwal rapat.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('EEEE, d MMMM yyyy');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Jadwalkan Rapat', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0.5,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextField(
              controller: _titleCtrl,
              decoration: InputDecoration(
                labelText: 'Judul Rapat *',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _descCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Deskripsi / Agenda (Opsional)',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),

            // Date picker
            ListTile(
              tileColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              leading: const Icon(Icons.calendar_today, color: Color(0xFF4F46E5)),
              title: const Text('Tanggal', style: TextStyle(fontSize: 12, color: Colors.grey)),
              subtitle: Text(dateFormat.format(_selectedDate), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              onTap: () async {
                final d = await showDatePicker(
                  context: context,
                  initialDate: _selectedDate,
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 365)),
                );
                if (d != null) setState(() => _selectedDate = d);
              },
            ),
            const SizedBox(height: 12),

            // Time picker
            ListTile(
              tileColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              leading: const Icon(Icons.access_time, color: Color(0xFF4F46E5)),
              title: const Text('Jam Mulai', style: TextStyle(fontSize: 12, color: Colors.grey)),
              subtitle: Text(_selectedTime.format(context), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              onTap: () async {
                final t = await showTimePicker(context: context, initialTime: _selectedTime);
                if (t != null) setState(() => _selectedTime = t);
              },
            ),
            const SizedBox(height: 16),

            // Duration
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int>(
                  value: _durationMinutes,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 30, child: Text('30 Menit')),
                    DropdownMenuItem(value: 60, child: Text('1 Jam (60 Menit)')),
                    DropdownMenuItem(value: 90, child: Text('1.5 Jam (90 Menit)')),
                    DropdownMenuItem(value: 120, child: Text('2 Jam (120 Menit)')),
                  ],
                  onChanged: (v) {
                    if (v != null) setState(() => _durationMinutes = v);
                  },
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Password
            TextField(
              controller: _pwdCtrl,
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Kata Sandi Ruang (Opsional)',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),

            // Toggles
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: Colors.white,
              elevation: 0,
              child: Column(
                children: [
                  SwitchListTile(
                    title: const Text('Izinkan Tamu Tanpa Akun', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    value: _allowGuests,
                    onChanged: (v) => setState(() => _allowGuests = v),
                  ),
                  const Divider(height: 1),
                  SwitchListTile(
                    title: const Text('Aktifkan Lobby (Ruang Tunggu)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    value: _lobbyEnabled,
                    onChanged: (v) => setState(() => _lobbyEnabled = v),
                  ),
                  const Divider(height: 1),
                  SwitchListTile(
                    title: const Text('Mulai dengan Mic Mati', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    value: _startMuted,
                    onChanged: (v) => setState(() => _startMuted = v),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            ElevatedButton(
              onPressed: _isLoading ? null : _submit,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4F46E5),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: _isLoading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Jadwalkan Rapat Sekarang', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}

