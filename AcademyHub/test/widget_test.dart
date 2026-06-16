import 'package:flutter_test/flutter_test.dart';

import 'package:academyhub/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const AcademyHubApp());

    // Verify that the App builds successfully.
    expect(find.byType(AcademyHubApp), findsOneWidget);

    // Pump frames to allow the post-frame callback navigation to run and route to resolve
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 500));
  });
}
