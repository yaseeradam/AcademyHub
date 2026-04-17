import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:animated_text_kit/animated_text_kit.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import 'package:flutter_staggered_animations/flutter_staggered_animations.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../auth/login_screen_new.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen>
    with TickerProviderStateMixin {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  
  late AnimationController _heartbeatController;
  late AnimationController _breathingController;
  late AnimationController _floatingController;
  
  final List<OnboardingPage> _pages = [
    OnboardingPage(
      title: "Welcome to Your\nEmotional Journey",
      subtitle: "Discover the power of emotional intelligence in education",
      icon: Icons.favorite_rounded,
      color: const Color(0xFFEC4899), // Pink 500
      emotion: "Joy",
      description: "Feel the excitement of learning with purpose and passion",
      particles: 15,
    ),
    OnboardingPage(
      title: "Connect with\nYour Learning",
      subtitle: "Build meaningful relationships through understanding",
      icon: Icons.psychology_rounded,
      color: const Color(0xFF8B5CF6), // Violet 500
      emotion: "Empathy",
      description: "Understand yourself and others on a deeper level",
      particles: 12,
    ),
    OnboardingPage(
      title: "Grow Through\nChallenges",
      subtitle: "Transform obstacles into opportunities for growth",
      icon: Icons.auto_awesome_rounded,
      color: const Color(0xFF10B981), // Emerald 500
      emotion: "Resilience",
      description: "Embrace challenges as stepping stones to success",
      particles: 18,
    ),
    OnboardingPage(
      title: "Create Your\nLegacy",
      subtitle: "Make a lasting impact through emotional wisdom",
      icon: Icons.rocket_launch_rounded,
      color: const Color(0xFFF59E0B), // Amber 500
      emotion: "Purpose",
      description: "Channel your emotions into meaningful achievements",
      particles: 20,
    ),
  ];

  @override
  void initState() {
    super.initState();
    _initAnimations();
  }

  void _initAnimations() {
    _heartbeatController = AnimationController(
      duration: const Duration(milliseconds: 1200),
      vsync: this,
    );
    
    _breathingController = AnimationController(
      duration: const Duration(seconds: 4),
      vsync: this,
    );
    
    _floatingController = AnimationController(
      duration: const Duration(seconds: 3),
      vsync: this,
    );

    _heartbeatController.repeat();
    _breathingController.repeat(reverse: true);
    _floatingController.repeat(reverse: true);
  }

  @override
  void dispose() {
    _heartbeatController.dispose();
    _breathingController.dispose();
    _floatingController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  void _nextPage() {
    if (_currentPage < _pages.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 800),
        curve: Curves.easeInOutCubic,
      );
    } else {
      _completeOnboarding();
    }
  }

  void _completeOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('onboarding_completed', true);
    
    if (mounted) {
      Navigator.of(context).pushReplacementNamed('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Animated background
          AnimatedContainer(
            duration: const Duration(milliseconds: 800),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  _pages[_currentPage].color.withOpacity(0.8),
                  _pages[_currentPage].color.withOpacity(0.6),
                  _pages[_currentPage].color.withOpacity(0.4),
                  Colors.white,
                ],
                stops: const [0.0, 0.3, 0.7, 1.0],
              ),
            ),
          ),
          
          // Floating particles
          ...List.generate(
            _pages[_currentPage].particles,
            (index) => _buildEmotionalParticle(index),
          ),
          
          // Main content
          PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() => _currentPage = index);
            },
            itemCount: _pages.length,
            itemBuilder: (context, index) {
              return _buildOnboardingPage(_pages[index], index);
            },
          ),
          
          // Bottom navigation
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: _buildBottomNavigation(),
          ),
        ],
      ),
    );
  }

  Widget _buildOnboardingPage(OnboardingPage page, int index) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 60),
            
            // Animated icon
            AnimationLimiter(
              child: AnimationConfiguration.synchronized(
                duration: const Duration(milliseconds: 1000),
                child: SlideAnimation(
                  verticalOffset: -50,
                  child: FadeInAnimation(
                    child: AnimatedBuilder(
                      animation: _heartbeatController,
                      builder: (context, child) {
                        final heartbeat = Tween<double>(
                          begin: 1.0,
                          end: 1.15,
                        ).animate(CurvedAnimation(
                          parent: _heartbeatController,
                          curve: Curves.easeInOut,
                        ));
                        
                        return Transform.scale(
                          scale: heartbeat.value,
                          child: Container(
                            width: 140,
                            height: 140,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: page.color.withOpacity(0.3),
                                  blurRadius: 30,
                                  spreadRadius: 10,
                                ),
                              ],
                            ),
                            child: Icon(
                              page.icon,
                              size: 70,
                              color: Colors.white,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
              ),
            ),
            
            const SizedBox(height: 40),
            
            // Emotion label
            AnimatedTextKit(
              key: ValueKey('emotion_$index'),
              animatedTexts: [
                TypewriterAnimatedText(
                  page.emotion,
                  textStyle: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.white.withOpacity(0.9),
                    letterSpacing: 2,
                  ),
                  speed: const Duration(milliseconds: 100),
                ),
              ],
              totalRepeatCount: 1,
            ).animate().fadeIn(delay: 500.ms),
            
            const SizedBox(height: 20),
            
            // Main title
            AnimatedTextKit(
              key: ValueKey('title_$index'),
              animatedTexts: [
                FadeAnimatedText(
                  page.title,
                  textStyle: const TextStyle(
                    fontSize: 36,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                    height: 1.2,
                  ),
                  duration: const Duration(milliseconds: 1500),
                ),
              ],
              totalRepeatCount: 1,
            ).animate().slideY(
              begin: 0.3,
              end: 0,
              delay: 800.ms,
              duration: 800.ms,
              curve: Curves.easeOutBack,
            ),
            
            const SizedBox(height: 24),
            
            // Subtitle
            Text(
              page.subtitle,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 18,
                color: Colors.white.withOpacity(0.8),
                fontWeight: FontWeight.w500,
                height: 1.4,
              ),
            ).animate().fadeIn(
              delay: 1200.ms,
              duration: 800.ms,
            ).slideY(
              begin: 0.2,
              end: 0,
              delay: 1200.ms,
              duration: 600.ms,
            ),
            
            const SizedBox(height: 40),
            
            // Description with breathing animation
            AnimatedBuilder(
              animation: _breathingController,
              builder: (context, child) {
                final breathing = Tween<double>(
                  begin: 0.95,
                  end: 1.05,
                ).animate(CurvedAnimation(
                  parent: _breathingController,
                  curve: Curves.easeInOut,
                ));
                
                return Transform.scale(
                  scale: breathing.value,
                  child: Container(
                    padding: const EdgeInsets.all(24),
                    margin: const EdgeInsets.symmetric(horizontal: 20),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: Colors.white.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Text(
                      page.description,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.white.withOpacity(0.9),
                        fontWeight: FontWeight.w400,
                        height: 1.5,
                      ),
                    ),
                  ),
                );
              },
            ).animate().fadeIn(
              delay: 1600.ms,
              duration: 1000.ms,
            ),
            
            const Spacer(),
          ],
        ),
      ),
    );
  }

  Widget _buildBottomNavigation() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.transparent,
            Colors.black.withOpacity(0.1),
            Colors.black.withOpacity(0.2),
          ],
        ),
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Page indicator
            AnimatedSmoothIndicator(
              activeIndex: _currentPage,
              count: _pages.length,
              effect: ExpandingDotsEffect(
                dotWidth: 12,
                dotHeight: 12,
                expansionFactor: 3,
                spacing: 8,
                activeDotColor: Colors.white,
                dotColor: Colors.white.withOpacity(0.4),
              ),
            ),
            
            const SizedBox(height: 32),
            
            // Action buttons
            Row(
              children: [
                if (_currentPage > 0)
                  Expanded(
                    child: TextButton(
                      onPressed: () {
                        _pageController.previousPage(
                          duration: const Duration(milliseconds: 600),
                          curve: Curves.easeInOut,
                        );
                      },
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: Text(
                        'Previous',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.8),
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  )
                else
                  const Expanded(child: SizedBox()),
                
                const SizedBox(width: 16),
                
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: _nextPage,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: _pages[_currentPage].color,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                      elevation: 8,
                      shadowColor: Colors.black.withOpacity(0.3),
                    ),
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 300),
                      child: Text(
                        _currentPage == _pages.length - 1 ? 'Begin Journey' : 'Continue',
                        key: ValueKey(_currentPage),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ).animate().scale(
                    delay: 2000.ms,
                    duration: 600.ms,
                    curve: Curves.easeOutBack,
                  ),
                ),
                
                const SizedBox(width: 16),
                
                Expanded(
                  child: TextButton(
                    onPressed: _completeOnboarding,
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: Text(
                      'Skip',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.6),
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmotionalParticle(int index) {
    final random = math.Random(index + _currentPage * 100);
    final size = random.nextDouble() * 6 + 3;
    final left = random.nextDouble() * MediaQuery.of(context).size.width;
    final top = random.nextDouble() * MediaQuery.of(context).size.height;
    final animationDuration = random.nextInt(4000) + 3000;
    final delay = random.nextInt(2000);

    return Positioned(
      left: left,
      top: top,
      child: AnimatedBuilder(
        animation: _floatingController,
        builder: (context, child) {
          final floating = Tween<double>(
            begin: -10,
            end: 10,
          ).animate(CurvedAnimation(
            parent: _floatingController,
            curve: Curves.easeInOut,
          ));
          
          return Transform.translate(
            offset: Offset(
              math.sin(_floatingController.value * 2 * math.pi) * 15,
              floating.value,
            ),
            child: Container(
              width: size,
              height: size,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.4),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: _pages[_currentPage].color.withOpacity(0.3),
                    blurRadius: 10,
                    spreadRadius: 2,
                  ),
                ],
              ),
            ),
          );
        },
      ).animate(onPlay: (controller) => controller.repeat(reverse: true))
          .fadeIn(
            delay: Duration(milliseconds: delay),
            duration: Duration(milliseconds: animationDuration),
          )
          .scale(
            begin: const Offset(0.5, 0.5),
            end: const Offset(1.2, 1.2),
            delay: Duration(milliseconds: delay),
            duration: Duration(milliseconds: animationDuration),
          ),
    );
  }
}

class OnboardingPage {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final String emotion;
  final String description;
  final int particles;

  OnboardingPage({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
    required this.emotion,
    required this.description,
    required this.particles,
  });
}