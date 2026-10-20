import 'dart:async';
import 'package:jitsi_meet_flutter_sdk/jitsi_meet_flutter_sdk.dart';
import 'api_service.dart';

class JitsiService {
  static final JitsiService _instance = JitsiService._internal();
  factory JitsiService() => _instance;
  JitsiService._internal();

  final JitsiMeet _jitsiMeet = JitsiMeet();
  Timer? _heartbeatTimer;
  String? _currentSessionToken;

  Future<void> joinConference({
    required String jitsiDomain,
    required String roomName,
    required String jwt,
    required String displayName,
    required String sessionToken,
    String? email,
    String? avatarUrl,
    bool startAudioMuted = false,
    bool startVideoMuted = false,
  }) async {
    _currentSessionToken = sessionToken;

    // Start 20-second heartbeat loop
    _startHeartbeat(sessionToken);

    final serverUrl = jitsiDomain.startsWith('http') ? jitsiDomain : 'https://$jitsiDomain';

    final options = JitsiMeetConferenceOptions(
      serverURL: serverUrl,
      room: roomName,
      token: jwt,
      configOverrides: {
        'startWithAudioMuted': startAudioMuted,
        'startWithVideoMuted': startVideoMuted,
        'subject': 'MeetHub Conference',
      },
      featureFlags: {
        'welcomepage.enabled': false,
        'prejoinpage.enabled': false,
        'screen-sharing.enabled': true,
        'chat.enabled': true,
        'raise-hand.enabled': true,
        'pip.enabled': true,
        'tile-view.enabled': true,
      },
      userInfo: JitsiMeetUserInfo(
        displayName: displayName,
        email: email,
        avatar: avatarUrl,
      ),
    );

    final listener = JitsiMeetEventListener(
      conferenceJoined: (url) {
        // Attendance join event
      },
      conferenceTerminated: (url, error) {
        _stopHeartbeat();
        if (_currentSessionToken != null) {
          ApiService().leaveMeeting(_currentSessionToken!);
          _currentSessionToken = null;
        }
      },
    );

    await _jitsiMeet.join(options, listener);
  }

  void _startHeartbeat(String sessionToken) {
    _heartbeatTimer?.cancel();
    _heartbeatTimer = Timer.periodic(const Duration(seconds: 20), (timer) {
      ApiService().sendHeartbeat(sessionToken);
    });
  }

  void _stopHeartbeat() {
    _heartbeatTimer?.cancel();
    _heartbeatTimer = null;
  }

  void hangUp() {
    _stopHeartbeat();
    _jitsiMeet.hangUp();
    if (_currentSessionToken != null) {
      ApiService().leaveMeeting(_currentSessionToken!);
      _currentSessionToken = null;
    }
  }
}

