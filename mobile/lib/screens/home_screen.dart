import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/meeting_model.dart';
import '../services/api_service.dart';
import '../services/jitsi_service.dart';
import 'join_with_code_screen.dart';
import 'login_screen.dart';
import 'meeting_create_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  List<MeetingModel> _meetings = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadMeetings();
  }

  Future<void> _loadMeetings() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getMeetings();
    setState(() {
      _meetings = list;
      _isLoading = false;
    });
  }

  Future<void> _startInstantMeeting() async {
    final user = ApiService().currentUser;
    final meeting = await ApiService().createInstantMeeting('Rapat Cepat ${user?.name ?? ''}');

    if (meeting != null) {
      final res = await ApiService().joinMeeting(meeting.roomCode);
      if (res['status'] == 'success') {
        await JitsiService().joinConference(
          jitsiDomain: res['jitsi_domain'],
          roomName: res['room_name'],
          jwt: res['jwt'],
          displayName: res['display_name'] ?? (user?.name ?? 'Host'),
          sessionToken: res['session_token'],
          email: user?.email,
        );
        _loadMeetings();
      }
    }
  }

  Future<void> _joinExisting(MeetingModel meeting) async {
    final user = ApiService().currentUser;
    final res = await ApiService().joinMeeting(meeting.roomCode);

    if (res['status'] == 'success') {
      await JitsiService().joinConference(
        jitsiDomain: res['jitsi_domain'],
        roomName: res['room_name'],
        jwt: res['jwt'],
        displayName: res['display_name'] ?? (user?.name ?? 'Peserta'),
        sessionToken: res['session_token'],
        email: user?.email,
      );
      _loadMeetings();
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Gagal bergabung ke rapat.')),
      );
    }
  }

  Widget _buildHomeTab() {
    final activeMeetings = _meetings.where((m) => m.isOngoing).toList();
    final upcomingMeetings = _meetings.where((m) => m.isScheduled).take(3).toList();

    return RefreshIndicator(
      onRefresh: _loadMeetings,
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // Instant meeting card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF4F46E5), Color(0xFF3730A3)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF4F46E5).withOpacity(0.3),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.videocam_rounded, color: Colors.white, size: 28),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Rapat Instan',
                  style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  'Mulai rapat video langsung tanpa batas durasi dalam 1 ketukan.',
                  style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 13),
                ),
                const SizedBox(height: 18),
                ElevatedButton.icon(
                  onPressed: _startInstantMeeting,
                  icon: const Icon(Icons.play_arrow_rounded, color: Color(0xFF4F46E5)),
                  label: const Text('Mulai Sekarang', style: TextStyle(fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: const Color(0xFF4F46E5),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Join with code button
          OutlinedButton.icon(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const JoinWithCodeScreen()),
            ),
            icon: const Icon(Icons.keyboard_outlined),
            label: const Text('Gabung dengan Kode / Tautan'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              side: BorderSide(color: Colors.grey.shade300),
              foregroundColor: const Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 24),

          // Active Meetings
          if (activeMeetings.isNotEmpty) ...[
            Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(color: Colors.emerald, shape: BoxShape.circle),
                ),
                const SizedBox(width: 8),
                const Text('Sedang Berlangsung', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 12),
            ...activeMeetings.map((m) => _buildMeetingCard(m, isOngoing: true)),
            const SizedBox(height: 16),
          ],

          // Upcoming Meetings Preview
          const Text('Jadwal Mendatang', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          if (upcomingMeetings.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text('Belum ada jadwal rapat mendatang.', style: TextStyle(color: Colors.grey, fontSize: 13)),
              ),
            )
          else
            ...upcomingMeetings.map((m) => _buildMeetingCard(m)),
        ],
      ),
    );
  }

  Widget _buildScheduleTab() {
    final upcoming = _meetings.where((m) => m.isScheduled).toList();
    return RefreshIndicator(
      onRefresh: _loadMeetings,
      child: upcoming.isEmpty
          ? const Center(child: Text('Tidak ada jadwal rapat.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: upcoming.length,
              itemBuilder: (ctx, i) => _buildMeetingCard(upcoming[i]),
            ),
    );
  }

  Widget _buildHistoryTab() {
    final ended = _meetings.where((m) => m.isEnded).toList();
    return RefreshIndicator(
      onRefresh: _loadMeetings,
      child: ended.isEmpty
          ? const Center(child: Text('Belum ada riwayat rapat.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: ended.length,
              itemBuilder: (ctx, i) => _buildMeetingCard(ended[i]),
            ),
    );
  }

  Widget _buildProfileTab() {
    final user = ApiService().currentUser;
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Center(
          child: CircleAvatar(
            radius: 40,
            backgroundColor: const Color(0xFF4F46E5).withOpacity(0.1),
            child: Text(
              user?.name.substring(0, 1).toUpperCase() ?? 'U',
              style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5)),
            ),
          ),
        ),
        const SizedBox(height: 16),
        Center(
          child: Text(
            user?.name ?? 'User',
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
        ),
        Center(
          child: Text(
            user?.email ?? '',
            style: const TextStyle(fontSize: 13, color: Colors.grey),
          ),
        ),
        const SizedBox(height: 8),
        Center(
          child: Chip(
            label: Text(user?.role.toUpperCase() ?? 'USER'),
            backgroundColor: const Color(0xFFEEF2FF),
            labelStyle: const TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 11),
          ),
        ),
        const SizedBox(height: 32),

        ListTile(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          tileColor: Colors.white,
          leading: const Icon(Icons.exit_to_app, color: Colors.rose),
          title: const Text('Keluar dari Akun', style: TextStyle(color: Colors.rose, fontWeight: FontWeight.bold)),
          onTap: () async {
            await ApiService().logout();
            if (!mounted) return;
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (_) => const LoginScreen()),
            );
          },
        ),
      ],
    );
  }

  Widget _buildMeetingCard(MeetingModel m, {bool isOngoing = false}) {
    final dateFormat = DateFormat('d MMM, HH:mm');

    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: isOngoing ? const Color(0xFFA7F3D0) : Colors.grey.shade200),
      ),
      color: isOngoing ? const Color(0xFFF0FDF4) : Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(m.roomCode, style: const TextStyle(fontFamily: 'monospace', fontSize: 12, color: Color(0xFF4F46E5), fontWeight: FontWeight.bold)),
                Text(dateFormat.format(m.scheduledAt), style: const TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
            const SizedBox(height: 8),
            Text(m.title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text('Host: ${m.hostName}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => _joinExisting(m),
                style: ElevatedButton.styleFrom(
                  backgroundColor: isOngoing ? const Color(0xFF059669) : const Color(0xFF4F46E5),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.symmetric(vertical: 10),
                ),
                child: Text(isOngoing ? 'Masuk Sekarang' : 'Gabung Rapat', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('MeetHub', style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
        backgroundColor: Colors.white,
        elevation: 0.5,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : IndexedStack(
              index: _currentIndex,
              children: [
                _buildHomeTab(),
                _buildScheduleTab(),
                _buildHistoryTab(),
                _buildProfileTab(),
              ],
            ),
      floatingActionButton: _currentIndex <= 1
          ? FloatingActionButton(
              onPressed: () async {
                final created = await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const MeetingCreateScreen()),
                );
                if (created == true) _loadMeetings();
              },
              backgroundColor: const Color(0xFF4F46E5),
              child: const Icon(Icons.add, color: Colors.white),
            )
          : null,
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Beranda'),
          NavigationDestination(icon: Icon(Icons.calendar_today_outlined), selectedIcon: Icon(Icons.calendar_today), label: 'Jadwal'),
          NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history), label: 'Riwayat'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}

