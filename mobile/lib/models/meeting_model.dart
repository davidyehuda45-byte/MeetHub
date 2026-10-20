class MeetingModel {
  final int id;
  final String title;
  final String? description;
  final String roomCode;
  final String jitsiRoomName;
  final DateTime scheduledAt;
  final int durationMinutes;
  final String status;
  final String hostName;
  final bool hasPassword;
  final bool lobbyEnabled;
  final bool allowGuests;
  final bool startMuted;

  MeetingModel({
    required this.id,
    required this.title,
    this.description,
    required this.roomCode,
    required this.jitsiRoomName,
    required this.scheduledAt,
    required this.durationMinutes,
    required this.status,
    required this.hostName,
    this.hasPassword = false,
    this.lobbyEnabled = false,
    this.allowGuests = true,
    this.startMuted = false,
  });

  factory MeetingModel.fromJson(Map<String, dynamic> json) {
    return MeetingModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? 'Rapat',
      description: json['description'] as String?,
      roomCode: json['room_code'] as String? ?? '',
      jitsiRoomName: json['jitsi_room_name'] as String? ?? '',
      scheduledAt: DateTime.tryParse(json['scheduled_at'] as String? ?? '') ?? DateTime.now(),
      durationMinutes: json['duration_minutes'] as int? ?? 60,
      status: json['status'] as String? ?? 'scheduled',
      hostName: (json['host'] != null && json['host']['name'] != null)
          ? json['host']['name'] as String
          : 'Host',
      hasPassword: json['password_hash'] != null && (json['password_hash'] as String).isNotEmpty,
      lobbyEnabled: json['lobby_enabled'] == 1 || json['lobby_enabled'] == true,
      allowGuests: json['allow_guests'] == null || json['allow_guests'] == 1 || json['allow_guests'] == true,
      startMuted: json['start_muted'] == 1 || json['start_muted'] == true,
    );
  }

  bool get isOngoing => status == 'ongoing';
  bool get isScheduled => status == 'scheduled';
  bool get isEnded => status == 'ended';
}

