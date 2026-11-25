import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';

enum BadgeType { green, blue, yellow, red, gray, dark }

class StatusBadge extends StatelessWidget {
  final String text;
  final BadgeType type;
  final double fontSize;
  final EdgeInsetsGeometry padding;

  const StatusBadge({
    super.key,
    required this.text,
    this.type = BadgeType.blue,
    this.fontSize = 9,
    this.padding = const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg;

    switch (type) {
      case BadgeType.green:
        bg = AppColors.successSoft;
        fg = const Color(0xFF065F46);
        break;
      case BadgeType.blue:
        bg = AppColors.accentSoft;
        fg = AppColors.accent;
        break;
      case BadgeType.yellow:
        bg = AppColors.warningSoft;
        fg = AppColors.yellowDark;
        break;
      case BadgeType.red:
        bg = AppColors.dangerSoft;
        fg = AppColors.danger;
        break;
      case BadgeType.gray:
        bg = const Color(0xFFF3F4F6);
        fg = AppColors.text;
        break;
      case BadgeType.dark:
        bg = AppColors.primary;
        fg = Colors.white;
        break;
    }

    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: fontSize,
          fontWeight: FontWeight.w700,
          color: fg,
        ),
      ),
    );
  }
}
