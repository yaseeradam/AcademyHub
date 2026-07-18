import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class ResultsChart extends StatelessWidget {
  final List<Map<String, dynamic>> subjectResults;

  const ResultsChart({super.key, required this.subjectResults});

  @override
  Widget build(BuildContext context) {
    if (subjectResults.isEmpty) {
      return const SizedBox.shrink();
    }

    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.analytics_outlined, color: AppColors.primaryBlue),
                SizedBox(width: 8),
                Text(
                  'Academic Progress Tracker',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                ),
              ],
            ),
            const SizedBox(height: 4),
            const Text(
              'Showing Term 2 CA1, CA2, and Exam score metrics',
              style: TextStyle(color: AppColors.textSecondary, fontSize: 12),
            ),
            const SizedBox(height: 24),
            SizedBox(
              height: 200,
              child: LineChart(
                LineChartData(
                  gridData: const FlGridData(show: false),
                  titlesData: FlTitlesData(
                    show: true,
                    topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          return Text(
                            value.toInt().toString(),
                            style: const TextStyle(color: AppColors.textSecondary, fontSize: 10),
                          );
                        },
                        reservedSize: 28,
                      ),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          int idx = value.toInt();
                          if (idx >= 0 && idx < subjectResults.length) {
                            String code = subjectResults[idx]['subject_code'] ?? '';
                            return Padding(
                              padding: const EdgeInsets.only(top: 8.0),
                              child: Text(
                                code,
                                style: const TextStyle(color: AppColors.textSecondary, fontSize: 10, fontWeight: FontWeight.bold),
                              ),
                            );
                          }
                          return const SizedBox.shrink();
                        },
                        reservedSize: 32,
                      ),
                    ),
                  ),
                  borderData: FlBorderData(show: false),
                  minX: 0,
                  maxX: (subjectResults.length - 1).toDouble(),
                  minY: 0,
                  maxY: 100, // Normalized percentage or total marks
                  lineBarsData: [
                    // CA1 (Max 20) Line
                    LineChartBarData(
                      spots: List.generate(subjectResults.length, (idx) {
                        final ca1 = double.tryParse(subjectResults[idx]['ca1'].toString()) ?? 0.0;
                        // Normalize to 100 scale for plotting
                        return FlSpot(idx.toDouble(), (ca1 / 20.0) * 100.0);
                      }),
                      isCurved: true,
                      color: AppColors.softBlue,
                      barWidth: 3,
                      isStrokeCapRound: true,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(show: false),
                    ),
                    // CA2 (Max 20) Line
                    LineChartBarData(
                      spots: List.generate(subjectResults.length, (idx) {
                        final ca2 = double.tryParse(subjectResults[idx]['ca2'].toString()) ?? 0.0;
                        return FlSpot(idx.toDouble(), (ca2 / 20.0) * 100.0);
                      }),
                      isCurved: true,
                      color: AppColors.accentAmber,
                      barWidth: 3,
                      isStrokeCapRound: true,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(show: false),
                    ),
                    // Exam (Max 60) Line
                    LineChartBarData(
                      spots: List.generate(subjectResults.length, (idx) {
                        final exam = double.tryParse(subjectResults[idx]['exam'].toString()) ?? 0.0;
                        return FlSpot(idx.toDouble(), (exam / 60.0) * 100.0);
                      }),
                      isCurved: true,
                      color: AppColors.successGreen,
                      barWidth: 3.5,
                      isStrokeCapRound: true,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(show: false),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            // Legend
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _buildLegendItem('CA 1', AppColors.softBlue),
                const SizedBox(width: 16),
                _buildLegendItem('CA 2', AppColors.accentAmber),
                const SizedBox(width: 16),
                _buildLegendItem('Exam', AppColors.successGreen),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLegendItem(String label, Color color) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(fontSize: 12, color: AppColors.textPrimary, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}
