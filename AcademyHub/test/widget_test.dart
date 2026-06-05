import 'package:flutter_test/flutter_test.dart';

import 'package:academyhub/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const AcademyHubApp());

    // Verify that the App builds successfully.
    expect(find.byType(AcademyHubApp), findsOneWidget);
  });
}
