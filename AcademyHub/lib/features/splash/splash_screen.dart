import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:animated_text_kit/animated_text_kit.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _pulseController;
  late AnimationController _rotationController;
  late AnimationController _scaleController;
  late Animation<double> _pulseAnimation;
  late Animation<double> _rotationAnimation;
  late Animation<double> _scaleAnimation;

  bool _showText = false;
  bool _showSubtitle = false;

  @override
  void initState() {
    super.initState();
    _initAnimations();
    _startAnimationSequence();
  }

  void _initAnimations() {
    _pulseController = AnimationController(
      duration: const Duration(seconds: 2),
      vsync: this,
    );

    _rotationController = AnimationController(
      duration: const Duration(seconds: 3),
      vsync: this,
    );

    _scaleController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _pulseAnimation = Tween<double>(
      begin: 0.8,
      end: 1.2,
    ).animate(CurvedAnimation(
      parent: _pulseController,
      curve: Curves.easeInOut,
    ));

    _rotationAnimation = Tween<double>(
      begin: 0,
      end: 2 * math.pi,
    ).animate(CurvedAnimation(
      parent: _rotationController,
      curve: Curves.easeInOut,
    ));

    _scaleAnimation = Tween<double>(
      begin: 0,
      end: 1,
    ).animate(CurvedAnimation(
      parent: _scaleController,
      curve: Curves.elasticOut,
    ));
  }

  void _startAnimationSequence() async {
    // Start scale animation
    _scaleController.forward();
    
    await Future.delayed(const Duration(milliseconds: 500));
    
    // Start pulse animation
    _pulseController.repeat(reverse: true);
    
    await Future.delayed(const Duration(milliseconds: 800));
    
    // Show main text
    setState(() => _showText = true);
    
    await Future.delayed(const Duration(milliseconds: 1200));
    
    // Show subtitle
    setState(() => _showSubtitle = true);
    
    await Future.delayed(const Duration(milliseconds: 2000));
    
    // Start rotation for exit
    _rotationController.forward();
    
    await Future.delayed(const Duration(milliseconds: 1500));
    
    // Navigate to next screen
    _navigateToNextScreen();
  }

  void _navigateToNextScreen() async {
    final authProvider = context.read<AuthProvider>();
    if (!mounted) return;

    if (authProvider.isAuthenticated) {
      context.go('/');
    } else {
      final isFirst = await authProvider.isFirstTime();
      if (!mounted) return;
      if (isFirst) {
        context.go('/onboarding');
      } else {
        context.go('/login');
      }
    }
  }

  @override
  void dispose() {
    _pulseController.dispose();
    _rotationController.dispose();
    _scaleController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF1E3A8A), // Blue 800
              Color(0xFF3B82F6), // Blue 500
              Color(0xFF06B6D4), // Cyan 500
              Color(0xFF10B981), // Emerald 500
            ],
            stops: [0.0, 0.3, 0.7, 1.0],
          ),
        ),
        child: Stack(
          children: [
            // Animated background particles
            ...List.generate(20, (index) => _buildFloatingParticle(index)),
            
            // Main content
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Animated logo
                  AnimatedBuilder(
                    animation: Listenable.merge([
                      _scaleAnimation,
                      _pulseAnimation,
                      _rotationAnimation,
                    ]),
                    builder: (context, child) {
                      return Transform.scale(
                        scale: _scaleAnimation.value * _pulseAnimation.value,
                        child: Transform.rotate(
                          angle: _rotationAnimation.value * 0.1,
                          child: Container(
                            width: 140,
                            height: 140,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(35),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.white.withValues(alpha: 0.3),
                                  blurRadius: 20,
                                  spreadRadius: 5,
                                ),
                                BoxShadow(
                                  color: const Color(0xFF3B82F6).withValues(alpha: 0.4),
                                  blurRadius: 40,
                                  spreadRadius: 10,
                                ),
                              ],
                            ),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(35),
                              child: Image.asset(
                                'lib/Alogo.png',
                                width: 140,
                                height: 140,
                                fit: BoxFit.contain,
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                  
                  const SizedBox(height: 40),
                  
                  // Animated main text
                  if (_showText)
                    AnimatedTextKit(
                      animatedTexts: [
                        TypewriterAnimatedText(
                          'AcademyHub',
                          textStyle: const TextStyle(
                            fontSize: 42,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                            letterSpacing: -1,
                            shadows: [
                              Shadow(
                                color: Colors.black26,
                                blurRadius: 10,
                                offset: Offset(0, 4),
                              ),
                            ],
                          ),
                          speed: const Duration(milliseconds: 150),
                        ),
                      ],
                      totalRepeatCount: 1,
                    ).animate().fadeIn(duration: 600.ms).slideY(
                          begin: 0.3,
                          end: 0,
                          duration: 800.ms,
                          curve: Curves.easeOutBack,
                        ),
                  
                  const SizedBox(height: 16),
                  
                  // Animated subtitle
                  if (_showSubtitle)
                    AnimatedTextKit(
                      animatedTexts: [
                        FadeAnimatedText(
                          'Empowering Education Through Intelligence',
                          textStyle: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w500,
                            color: Colors.white.withValues(alpha: 0.9),
                            letterSpacing: 0.5,
                          ),
                          duration: const Duration(milliseconds: 2000),
                        ),
                      ],
                      totalRepeatCount: 1,
                    ).animate().fadeIn(
                          delay: 300.ms,
                          duration: 800.ms,
                        ).slideY(
                          begin: 0.2,
                          end: 0,
                          delay: 300.ms,
                          duration: 600.ms,
                        ),
                  
                  const SizedBox(height: 60),
                  
                  // Loading indicator
                  if (_showSubtitle)
                    Column(
                      children: [
                        SizedBox(
                          width: 40,
                          height: 40,
                          child: CircularProgressIndicator(
                            strokeWidth: 3,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              Colors.white.withValues(alpha: 0.8),
                            ),
                          ),
                        ).animate(onPlay: (controller) => controller.repeat())
                            .rotate(duration: 2000.ms),
                        
                        const SizedBox(height: 20),
                        
                        Text(
                          'Preparing your experience...',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.white.withValues(alpha: 0.7),
                            fontWeight: FontWeight.w400,
                          ),
                        ).animate().fadeIn(
                              delay: 800.ms,
                              duration: 600.ms,
                            ),
                      ],
                    ),
                ],
              ),
            ),
            
            // Bottom branding
            Positioned(
              bottom: 50,
              left: 0,
              right: 0,
              child: Column(
                children: [
                  Text(
                    'Powered by Emotional Intelligence',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.white.withValues(alpha: 0.6),
                      fontWeight: FontWeight.w500,
                      letterSpacing: 1,
                    ),
                  ).animate().fadeIn(
                        delay: 2000.ms,
                        duration: 800.ms,
                      ),
                  
                  const SizedBox(height: 8),
                  
                  Container(
                    width: 60,
                    height: 2,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.transparent,
                          Colors.white.withValues(alpha: 0.5),
                          Colors.transparent,
                        ],
                      ),
                    ),
                  ).animate().scaleX(
                        delay: 2200.ms,
                        duration: 800.ms,
                        curve: Curves.easeOutBack,
                      ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFloatingParticle(int index) {
    final random = math.Random(index);
    final size = random.nextDouble() * 4 + 2;
    final left = random.nextDouble() * MediaQuery.of(context).size.width;
    final animationDuration = random.nextInt(3000) + 2000;
    final delay = random.nextInt(2000);

    return Positioned(
      left: left,
      top: random.nextDouble() * MediaQuery.of(context).size.height,
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.3),
          shape: BoxShape.circle,
        ),
      ).animate(onPlay: (controller) => controller.repeat(reverse: true))
          .fadeIn(
            delay: Duration(milliseconds: delay),
            duration: Duration(milliseconds: animationDuration),
          )
          .moveY(
            begin: 0,
            end: -50,
            delay: Duration(milliseconds: delay),
            duration: Duration(milliseconds: animationDuration),
          ),
    );
  }
}