import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../providers/auth_provider.dart';

class FloatingWhatsAppButton extends StatelessWidget {
  const FloatingWhatsAppButton({super.key});

  @override
  Widget build(BuildContext context) {
    return FloatingActionButton.extended(
      onPressed: () {
        context.read<AuthProvider>().launchWhatsApp();
      },
      backgroundColor: const Color(0xFF25D366),
      icon: const Text('💬', style: TextStyle(fontSize: 18)),
      label: const Text(
        'Chat CS',
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w800,
          color: Colors.white,
        ),
      ),
      elevation: 4,
    );
  }
}
