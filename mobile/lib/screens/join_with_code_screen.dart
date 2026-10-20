import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/jitsi_service.dart';

class JoinWithCodeScreen extends StatefulWidget {
  final String? initialCode;
  const JoinWithCodeScreen({super.key, this.initialCode});

  @override
  State<JoinWithCodeScreen> createState() => _JoinWithCodeScreenState();
}

class _JoinWithCodeScreenState extends State<JoinWithCodeScreen> {
  late TextEditingController _codeCtrl;
  final _nameCtrl = TextEditingController();
  final _pwdCtrl = TextEditingController();

  bool _startAudioMuted = false;
  bool _startVideoMuted = false;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _codeCtrl = TextEditingController(text: widget.initialCode ?? '');
    final user = ApiService().currentUser;
    if (user != null) {
      _nameCtrl.text = user.name;
    }
  }

  Future<void> _join() async {
    final code = _codeCtrl.text.trim();
    if (code.isEmpty) {
      setState(() => _errorMessage = 'Masukkan kode rapat.');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService().joinMeeting(
      code,
      guestName: _nameCtrl.text.trim().isEmpty ? null : _nameCtrl.text.trim(),
      password: _pwdCtrl.text.trim().isEmpty ? null : _pwdCtrl.text.trim(),
    );

    setState(() => _isLoading = false);

    if (res['status'] == 'success') {
      final user = ApiService().currentUser;

      await JitsiService().joinConference(
        jitsiDomain: res['jitsi_domain'],
        roomName: res['room_name'],
        jwt: res['jwt'],
        displayName: res['display_name'] ?? (user?.name ?? 'Peserta Mobile'),
        sessionToken: res['session_token'],
        email: user?.email,
        startAudioMuted: _startAudioMuted,
        startVideoMuted: _startVideoMuted,
      );

      if (mounted) {
        Navigator.pop(context);
      }
    } else {
      setState(() {
        _errorMessage = res['message'] ?? 'Gagal bergabung ke rapat.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Gabung Rapat', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
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
            if (_errorMessage != null)
              Container(
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF1F2),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFECDD3)),
                ),
                child: Text(_errorMessage!, style: const TextStyle(color: Color(0xFFE11D48), fontSize: 13)),
              ),

            // Code
            TextField(
              controller: _codeCtrl,
              decoration: InputDecoration(
                labelText: 'Kode Ruang Rapat',
                hintText: 'Contoh: mh-xxxx-xxxx',
                prefixIcon: const Icon(Icons.meeting_room_outlined),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),

            // Name
            TextField(
              controller: _nameCtrl,
              decoration: InputDecoration(
                labelText: 'Nama Tampilan Anda',
                prefixIcon: const Icon(Icons.person_outline),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),

            // Password
            TextField(
              controller: _pwdCtrl,
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Kata Sandi Rapat (Bila Ada)',
                prefixIcon: const Icon(Icons.key_outlined),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 24),

            // Toggles
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              elevation: 0,
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Column(
                  children: [
                    SwitchListTile(
                      title: const Text('Mulai dengan Mic Bisu', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Mikrofon mati saat masuk', style: TextStyle(fontSize: 12)),
                      value: _startAudioMuted,
                      onChanged: (v) => setState(() => _startAudioMuted = v),
                    ),
                    const Divider(height: 1),
                    SwitchListTile(
                      title: const Text('Mulai dengan Kamera Mati', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Kamera mati saat masuk', style: TextStyle(fontSize: 12)),
                      value: _startVideoMuted,
                      onChanged: (v) => setState(() => _startVideoMuted = v),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 28),

            ElevatedButton(
              onPressed: _isLoading ? null : _join,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4F46E5),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: _isLoading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Masuk ke Rapat Sekarang', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}

