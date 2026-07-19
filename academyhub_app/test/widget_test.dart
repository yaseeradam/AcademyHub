import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:academyhub_app/main.dart';
import 'package:academyhub_app/features/auth/presentation/school_finder_screen.dart';

void main() {
  testWidgets('School Finder screen elements render successfully', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const ProviderScope(
      child: MaterialApp(
        home: SchoolFinderScreen(),
      ),
    ));

    // Verify that the School Finder screen is initial route and renders
    expect(find.byType(SchoolFinderScreen), findsOneWidget);

    // Verify key titles and logo details are present
    expect(find.text('AcademyHub'), findsOneWidget);
    expect(find.text('Your School Portal'), findsOneWidget);
    expect(find.byIcon(Icons.school_rounded), findsWidgets);

    // Verify text field exists for slug lookup
    expect(find.byType(TextField), findsOneWidget);
    expect(find.text('e.g. greenwood'), findsOneWidget); // Hint text

    // Verify the Continue button is initially disabled (can't proceed without slug validation)
    final ElevatedButton button = tester.widget<ElevatedButton>(
      find.widgetWithText(ElevatedButton, 'Continue'),
    );
    expect(button.enabled, isFalse);
  });
}
