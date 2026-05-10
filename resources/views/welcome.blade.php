<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MyAcademy') }} - A Joyful Place to Learn</title>
    
    <!-- Playful Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc; /* Very light slate */
            color: #334155;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Fredoka', sans-serif;
        }

        /* Playful Liquid Elements */
        .blob-bg {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        .blob-bg-1 { top: -10%; left: -5%; width: 500px; height: 500px; background: rgba(139, 92, 246, 0.4); animation-delay: 0s; }
        .blob-bg-2 { top: 20%; right: -5%; width: 600px; height: 600px; background: rgba(56, 189, 248, 0.3); animation-delay: 2s; }
        .blob-bg-3 { bottom: -10%; left: 20%; width: 400px; height: 400px; background: rgba(251, 146, 60, 0.3); animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }
        
        @keyframes sway {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        .animate-sway {
            animation: sway 4s ease-in-out infinite;
        }

        /* Nav Glass */
        .nav-scrolled {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid rgba(241, 245, 249, 1);
        }

        /* Playful Buttons */
        .btn-playful {
            background: linear-gradient(135deg, #8b5cf6, #d946ef); /* Violet to Fuchsia */
            color: white;
            box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.5);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); /* Bouncy easing */
        }
        .btn-playful:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 15px 30px -5px rgba(139, 92, 246, 0.6);
        }
        
        .btn-outline-playful {
            background: white;
            color: #8b5cf6;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .btn-outline-playful:hover {
            border-color: #8b5cf6;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.2);
        }

        /* Playful Cards */
        .card-liquid {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border: 2px solid rgba(255, 255, 255, 1);
            border-radius: 32px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05), inset 0 0 0 2px rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .card-liquid:hover {
            transform: translateY(-8px) rotate(1deg);
            box-shadow: 0 30px 50px -10px rgba(139, 92, 246, 0.15), inset 0 0 0 2px rgba(255, 255, 255, 0.8);
        }
        
        .floating-element {
            animation: float-small 6s ease-in-out infinite;
        }
        .floating-delayed {
            animation: float-small 7s ease-in-out infinite;
            animation-delay: 1.5s;
        }

        @keyframes float-small {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .gradient-text {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to right, #8b5cf6, #38bdf8);
        }

        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 2rem;
            box-shadow: 0 8px 16px -4px rgba(0,0,0,0.1);
        }
        
        /* Dashed connecting line for journey section */
        .dashed-line {
            background-image: linear-gradient(90deg, #cbd5e1 50%, transparent 50%);
            background-size: 20px 2px;
            background-repeat: repeat-x;
            height: 2px;
            opacity: 0.6;
        }
    </style>
</head>
<body class="antialiased" x-data="{ mobileMenuOpen: false, demoModalOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Background Blobs -->
    <div class="blob-bg blob-bg-1"></div>
    <div class="blob-bg blob-bg-2"></div>
    <div class="blob-bg blob-bg-3"></div>

    <!-- Joyful Navbar -->
    <nav :class="{ 'nav-scrolled py-3': scrolled, 'bg-transparent py-6': !scrolled }" class="fixed top-0 z-50 w-full transition-all duration-300">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5 flex items-center gap-3 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-violet-500 to-fuchsia-500 shadow-lg text-white font-heading text-2xl group-hover:rotate-12 transition-transform duration-300">
                            🎒
                        </div>
                        <span class="text-3xl font-heading font-extrabold text-slate-800 tracking-tight">MyAcademy</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex lg:gap-x-10">
                    <a href="#modules" class="text-base font-bold text-slate-600 hover:text-violet-500 transition-colors">Modules</a>
                    <a href="#portals" class="text-base font-bold text-slate-600 hover:text-pink-500 transition-colors">Portals</a>
                    <a href="#journey" class="text-base font-bold text-slate-600 hover:text-sky-500 transition-colors">How it Works</a>
                </div>

                <!-- Desktop Actions -->
                <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center lg:gap-4">
                    <button @click="demoModalOpen = true" class="btn-outline-playful rounded-full px-5 py-2.5 text-base font-bold flex items-center gap-2">
                        ✨ Start Tour
                    </button>
                    <a href="{{ route('login') }}" class="btn-playful rounded-full px-6 py-2.5 text-base font-bold">
                        Log In 🚀
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex lg:hidden">
                    <button @click="mobileMenuOpen = true" type="button" class="-m-2.5 inline-flex items-center justify-center rounded-xl p-2.5 text-slate-700 bg-white shadow-sm border border-slate-200">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m-15 6h15m-15-12h15" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" class="lg:hidden" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 z-50 bg-slate-800/40 backdrop-blur-sm"></div>
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="translate-x-full" 
                 class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white/95 backdrop-blur-xl px-6 py-6 sm:max-w-sm">
                
                <div class="flex items-center justify-between mb-8">
                    <a href="#" class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-violet-500 to-fuchsia-500 text-white text-2xl">🎒</div>
                        <span class="text-2xl font-heading font-extrabold text-slate-800">MyAcademy</span>
                    </a>
                    <button @click="mobileMenuOpen = false" type="button" class="-m-2.5 rounded-full p-2.5 text-slate-500 hover:bg-slate-100 transition">
                        <span class="sr-only">Close menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <a href="#modules" @click="mobileMenuOpen = false" class="block rounded-2xl px-4 py-3 text-lg font-bold text-slate-700 hover:bg-violet-50 hover:text-violet-600 transition">Modules 🧩</a>
                    <a href="#portals" @click="mobileMenuOpen = false" class="block rounded-2xl px-4 py-3 text-lg font-bold text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition">Portals 🏫</a>
                    <a href="#journey" @click="mobileMenuOpen = false" class="block rounded-2xl px-4 py-3 text-lg font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition">How it Works 🗺️</a>
                    
                    <div class="pt-6 mt-6 border-t border-slate-100 flex flex-col gap-3">
                        <button @click="mobileMenuOpen = false; demoModalOpen = true" class="w-full text-center btn-outline-playful rounded-full px-6 py-3 text-lg font-bold">
                            ✨ Take a Tour
                        </button>
                        <a href="{{ route('login') }}" class="w-full text-center btn-playful rounded-full px-6 py-3 text-lg font-bold">
                            Log In 🚀
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="relative isolate pt-32 lg:pt-40 pb-20">
        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-6 lg:px-8 relative">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <!-- Text Content -->
                <div class="lg:w-1/2 text-center lg:text-left z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-violet-100 shadow-sm mb-6 floating-element">
                        <span class="flex h-3 w-3 rounded-full bg-emerald-400"></span>
                        <span class="text-sm font-bold text-slate-600">The Ultimate School Management System 🎉</span>
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-heading font-extrabold tracking-tight text-slate-800 leading-tight mb-6">
                        Running a school <br class="hidden lg:block"/> has never been <span class="gradient-text">this fun!</span>
                    </h1>
                    <p class="text-xl text-slate-600 mb-10 leading-relaxed font-medium">
                        Replace your boring spreadsheets with a magical digital playground. MyAcademy connects parents, excites students, empowers teachers, and gives admins total peace of mind.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('login') }}" class="w-full sm:w-auto btn-playful rounded-full px-8 py-4 text-lg font-bold text-center">
                            Start Free Trial 🎈
                        </a>
                        <button @click="demoModalOpen = true" class="w-full sm:w-auto btn-outline-playful rounded-full px-8 py-4 text-lg font-bold">
                            View Demo 🔑
                        </button>
                    </div>
                </div>

                <!-- Playful Hero Visual -->
                <div class="lg:w-1/2 relative h-[500px] w-full mt-10 lg:mt-0">
                    <!-- Main Dashboard Window -->
                    <div class="absolute inset-0 bg-white/80 backdrop-blur-xl rounded-[40px] border-[3px] border-white shadow-[0_30px_60px_-15px_rgba(139,92,246,0.3)] overflow-hidden flex flex-col p-6 z-10 transform translate-y-4">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex gap-2">
                                <div class="w-4 h-4 rounded-full bg-red-400"></div>
                                <div class="w-4 h-4 rounded-full bg-amber-400"></div>
                                <div class="w-4 h-4 rounded-full bg-emerald-400"></div>
                            </div>
                            <div class="px-4 py-1.5 bg-violet-100 text-violet-600 rounded-full text-sm font-bold animate-pulse">System Online ✅</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Mini Card -->
                            <div class="bg-gradient-to-br from-orange-300 to-orange-400 p-4 rounded-3xl text-white shadow-sm">
                                <div class="text-3xl mb-1">💸</div>
                                <div class="text-sm font-bold opacity-90">Fees Collected</div>
                                <div class="text-2xl font-heading font-bold">94%</div>
                            </div>
                            <!-- Mini Card -->
                            <div class="bg-gradient-to-br from-emerald-300 to-emerald-400 p-4 rounded-3xl text-white shadow-sm">
                                <div class="text-3xl mb-1">👩‍🎓</div>
                                <div class="text-sm font-bold opacity-90">Attendance</div>
                                <div class="text-2xl font-heading font-bold">1,204 Active</div>
                            </div>
                        </div>
                        
                        <div class="flex-1 bg-slate-50 rounded-3xl border border-slate-100 p-4 relative overflow-hidden flex flex-col gap-3">
                            <div class="w-1/2 h-4 bg-slate-200 rounded-full"></div>
                            <div class="w-full h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center px-4 gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm">📅</div>
                                <div class="w-1/2 h-3 bg-slate-200 rounded-full"></div>
                            </div>
                             <div class="w-full h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center px-4 gap-3">
                                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-sm">📝</div>
                                <div class="w-2/3 h-3 bg-slate-200 rounded-full"></div>
                            </div>
                            <div class="w-full h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center px-4 gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm">🚌</div>
                                <div class="w-1/3 h-3 bg-slate-200 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating decorative bubbles -->
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-yellow-300 rounded-3xl shadow-xl floating-element rotate-12 flex items-center justify-center text-4xl border-4 border-white z-20">✏️</div>
                    
                    <div class="absolute top-40 -left-12 w-20 h-20 bg-sky-400 rounded-full shadow-lg floating-delayed flex items-center justify-center text-3xl border-4 border-white z-20">🚌</div>
                    
                    <div class="absolute -bottom-8 right-10 w-28 h-28 bg-pink-400 rounded-[30px] shadow-xl floating-element flex items-center justify-center text-5xl border-4 border-white z-20 transform -rotate-12 animate-sway">🔔</div>
                </div>
            </div>
        </div>

        <!-- Trust / Stats Banner -->
        <div class="mt-24 max-w-7xl mx-auto px-6 lg:px-8">
            <div class="card-liquid p-6 md:p-8 rounded-[40px] flex flex-col md:flex-row items-center justify-around gap-6 text-center">
                <div>
                    <div class="text-4xl font-heading font-extrabold text-violet-500 mb-1">0</div>
                    <div class="font-bold text-slate-600">Boring Spreadsheets</div>
                </div>
                <!-- Divider -->
                <div class="h-12 w-1 bg-slate-200 rounded-full hidden md:block"></div>
                <div>
                    <div class="text-4xl font-heading font-extrabold text-orange-500 mb-1">100%</div>
                    <div class="font-bold text-slate-600">Cloud Data Security</div>
                </div>
                <!-- Divider -->
                <div class="h-12 w-1 bg-slate-200 rounded-full hidden md:block"></div>
                <div>
                    <div class="text-4xl font-heading font-extrabold text-emerald-500 mb-1">20+</div>
                    <div class="font-bold text-slate-600">Powerful Modules</div>
                </div>
            </div>
        </div>

        <!-- Comprehensive Modules Section -->
        <div id="modules" class="mt-32 max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-sky-500 font-bold tracking-wider uppercase text-sm">Everything in one place</span>
                <h2 class="text-4xl md:text-5xl font-heading font-bold text-slate-800 mt-2">All the tools you need 🧩</h2>
                <p class="text-lg text-slate-600 mt-4 max-w-2xl mx-auto font-medium">MyAcademy is a unified School Management System. We've packed it with delightfully simple modules to manage every single aspect of your institution.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Fee Management -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-violet-300 transition-colors group">
                    <div class="w-16 h-16 bg-violet-100 text-violet-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">💰</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Fee Management</h3>
                    <p class="text-slate-600 font-medium">Collect fees, generate PDF receipts, track outstanding balances, and let the bursar manage finances without touching a spreadsheet.</p>
                </div>

                <!-- CBT Exams -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-emerald-300 transition-colors group">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">🖥️</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Computer Based Testing</h3>
                    <p class="text-slate-600 font-medium">Students sit timed online exams from their portal. Auto-graded instantly — no marking, no waiting, no bias.</p>
                </div>

                <!-- Report Cards & Certificates -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-pink-300 transition-colors group">
                    <div class="w-16 h-16 bg-pink-100 text-pink-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">📄</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Report Cards & Certificates</h3>
                    <p class="text-slate-600 font-medium">One click generates beautiful PDF report cards with grades, remarks, and attendance. Award certificates with custom designs.</p>
                </div>

                <!-- Attendance Tracking -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-sky-300 transition-colors group">
                    <div class="w-16 h-16 bg-sky-100 text-sky-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">✅</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Attendance Tracking</h3>
                    <p class="text-slate-600 font-medium">Teachers mark daily attendance in seconds. Parents get notified of absences and can view their child's attendance history anytime.</p>
                </div>

                <!-- Homework -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-orange-300 transition-colors group">
                    <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">📚</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Homework & Submissions</h3>
                    <p class="text-slate-600 font-medium">Teachers assign homework with deadlines. Students submit directly from their portal. Track completion rates and grade on the spot.</p>
                </div>

                <!-- Performance Analytics -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-indigo-300 transition-colors group">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">📈</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Performance Analytics</h3>
                    <p class="text-slate-600 font-medium">Deep-dive dashboards show strengths, weaknesses, and trends per student. Spot struggling pupils early and intervene before it's too late.</p>
                </div>

                <!-- WhatsApp Bot -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-green-300 transition-colors group">
                    <div class="w-16 h-16 bg-green-100 text-green-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">💬</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">WhatsApp Bot Integration</h3>
                    <p class="text-slate-600 font-medium">Parents query results, fees, and attendance directly on WhatsApp. No app download needed — it just works where they already are.</p>
                </div>

                <!-- Student & Staff Management -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-amber-300 transition-colors group">
                    <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">🏫</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Student & Staff Records</h3>
                    <p class="text-slate-600 font-medium">Centralised profiles for every student and staff member — admission forms, class assignments, subjects, and documents all in one place.</p>
                </div>

                <!-- Backup & Restore -->
                <div class="bg-white rounded-[32px] p-8 shadow-sm border-2 border-slate-100 hover:border-rose-300 transition-colors group">
                    <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">🛡️</div>
                    <h3 class="text-xl font-heading font-bold text-slate-800 mb-3">Backup & Restore</h3>
                    <p class="text-slate-600 font-medium">One-click full backup zips your database and all uploads. Restore in minutes — your school's data is always safe and recoverable.</p>
                </div>
            </div>
        </div>

        <!-- The Journey (How it Works) -->
        <div id="journey" class="mt-32 max-w-7xl mx-auto px-6 lg:px-8 py-16">
             <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-heading font-bold text-slate-800">The Ultimate Learning Journey 🗺️</h2>
                <p class="text-lg text-slate-600 mt-4 max-w-2xl mx-auto font-medium">See how seamlessly everything connects to make life easier for everyone involved.</p>
            </div>

            <!-- Timeline Diagram -->
            <div class="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-8 md:gap-4 px-4 w-full max-w-5xl mx-auto">
                <!-- Line -->
                <div class="absolute top-1/2 left-0 right-0 hidden md:block dashed-line -z-10 translate-y-[-120%]"></div>
                
                <!-- Step 1 Admin -->
                <div class="flex flex-col items-center text-center max-w-[200px] z-10 bg-slate-50">
                    <div class="w-24 h-24 rounded-full bg-white border-4 border-violet-200 shadow-md flex items-center justify-center text-5xl mb-4 floating-element hover:rotate-12 transition">👑</div>
                    <h4 class="font-heading font-bold text-slate-800 text-lg">1. Admins set up</h4>
                    <p class="text-sm text-slate-500 mt-2">Admins create the session, add fees, and register pupils.</p>
                </div>
                
                <!-- Step 2 Teacher -->
                <div class="flex flex-col items-center text-center max-w-[200px] z-10 bg-slate-50">
                    <div class="w-24 h-24 rounded-full bg-white border-4 border-emerald-200 shadow-md flex items-center justify-center text-5xl mb-4 floating-delayed hover:-rotate-12 transition">👩‍🏫</div>
                    <h4 class="font-heading font-bold text-slate-800 text-lg">2. Teachers grade</h4>
                    <p class="text-sm text-slate-500 mt-2">Teachers log in to assign homework, take attendance, and input marks.</p>
                </div>

                <!-- Step 3 Student -->
                <div class="flex flex-col items-center text-center max-w-[200px] z-10 bg-slate-50">
                    <div class="w-24 h-24 rounded-full bg-white border-4 border-sky-200 shadow-md flex items-center justify-center text-5xl mb-4 floating-element hover:rotate-12 transition">🎒</div>
                    <h4 class="font-heading font-bold text-slate-800 text-lg">3. Students learn</h4>
                    <p class="text-sm text-slate-500 mt-2">Students view their progress, read announcements, and take CBTs.</p>
                </div>

                <!-- Step 4 Parent -->
                <div class="flex flex-col items-center text-center max-w-[200px] z-10 bg-slate-50">
                    <div class="w-24 h-24 rounded-full bg-white border-4 border-pink-200 shadow-md flex items-center justify-center text-5xl mb-4 floating-delayed hover:-rotate-12 transition">👨‍👩‍👦</div>
                    <h4 class="font-heading font-bold text-slate-800 text-lg">4. Parents smile</h4>
                    <p class="text-sm text-slate-500 mt-2">Parents automatically get notified, view receipts, and download reports.</p>
                </div>
            </div>
        </div>

        <!-- Portals Section (Learning Zones) -->
        <div id="portals" class="mt-24 max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-violet-500 font-bold tracking-wider uppercase text-sm">Everyone Belongs Here</span>
                <h2 class="text-4xl md:text-5xl font-heading font-bold text-slate-800 mt-2">Dedicated Spaces 🏘️</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Admin Card -->
                <div class="card-liquid p-8 flex flex-col items-center text-center group">
                    <div class="w-20 h-20 rounded-full bg-violet-100 text-violet-500 flex items-center justify-center text-4xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                        👑
                    </div>
                    <h3 class="text-2xl font-heading font-bold text-slate-800 mb-2">Administrators</h3>
                    <p class="text-slate-600 font-medium text-sm">Manage entire operations playfully.</p>
                </div>
                
                <!-- Teacher Card -->
                <div class="card-liquid p-8 flex flex-col items-center text-center group" style="animation-delay: 0.1s">
                    <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-4xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                        👩‍🏫
                    </div>
                    <h3 class="text-2xl font-heading font-bold text-slate-800 mb-2">Teachers</h3>
                    <p class="text-slate-600 font-medium text-sm">Grade effectively with joy, not stress.</p>
                </div>
                
                <!-- Student Card -->
                <div class="card-liquid p-8 flex flex-col items-center text-center group" style="animation-delay: 0.2s">
                    <div class="w-20 h-20 rounded-full bg-sky-100 text-sky-500 flex items-center justify-center text-4xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                        🎒
                    </div>
                    <h3 class="text-2xl font-heading font-bold text-slate-800 mb-2">Students</h3>
                    <p class="text-slate-600 font-medium text-sm">A fun dashboard connecting their world.</p>
                </div>
                
                <!-- Parent Card -->
                <div class="card-liquid p-8 flex flex-col items-center text-center group" style="animation-delay: 0.3s">
                    <div class="w-20 h-20 rounded-full bg-pink-100 text-pink-500 flex items-center justify-center text-4xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                        👨‍👩‍👦
                    </div>
                    <h3 class="text-2xl font-heading font-bold text-slate-800 mb-2">Parents</h3>
                    <p class="text-slate-600 font-medium text-sm">Always in the loop, always happy.</p>
                </div>
            </div>
        </div>

        <!-- CTA Space -->
        <div class="mt-40 max-w-5xl mx-auto px-6 pb-20">
            <div class="card-liquid bg-gradient-to-tr from-violet-100 to-sky-100 border-none p-12 lg:p-20 text-center relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white rounded-full opacity-60 blur-2xl"></div>
                <div class="absolute top-10 left-10 text-6xl opacity-30 transform -rotate-12">🎈</div>
                <div class="absolute bottom-10 right-10 text-6xl opacity-30 transform rotate-12">✨</div>
                
                <h2 class="text-5xl font-heading font-extrabold text-slate-800 mb-6 relative z-10">Ready to join the fun? 🚀</h2>
                <p class="text-xl text-slate-600 mb-10 font-medium relative z-10">Start exploring our playful school management ecosystem today and give your institution the modern upgrade it deserves.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
                    <a href="{{ route('login') }}" class="btn-playful rounded-full px-12 py-5 text-xl font-bold">Go to Login 🚪</a>
                    <button @click="demoModalOpen = true" class="btn-outline-playful rounded-full px-12 py-5 text-xl font-bold bg-white">View Demo Codes 🔑</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 pt-16 pb-8 text-center relative overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-slate-50 rounded-full blur-3xl -z-10 mt-20"></div>
        <div class="flex items-center justify-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-violet-500 to-fuchsia-500 text-white text-xl">🎒</div>
            <span class="text-2xl font-heading font-extrabold text-slate-800 tracking-tight">MyAcademy</span>
        </div>
        <p class="text-slate-500 font-medium">Making school management a joyful breeze.</p>
        <p class="text-slate-400 text-sm mt-8">&copy; {{ date('Y') }} MyAcademy Inc. All smiles reserved. 😊</p>
    </footer>

    <!-- Playful Demo Modal -->
    <div x-show="demoModalOpen" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Soft blurry backdrop -->
        <div x-show="demoModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="demoModalOpen" 
                     x-transition:enter="cubic-bezier(0.34, 1.56, 0.64, 1) duration-500"
                     x-transition:enter-start="opacity-0 translate-y-16 sm:scale-75"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                     @click.away="demoModalOpen = false"
                     class="relative transform overflow-hidden rounded-[40px] bg-white/95 backdrop-blur-xl border border-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl p-6 lg:p-10">
                    
                    <button @click="demoModalOpen = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-800 hover:rotate-90 transition-all bg-slate-100 hover:bg-slate-200 rounded-full p-3 shadow-inner">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="text-center mb-10 mt-4">
                        <div class="inline-flex h-20 w-20 items-center justify-center rounded-[24px] bg-gradient-to-tr from-sky-300 to-indigo-400 text-white text-4xl mb-4 shadow-lg transform rotate-3">
                            🔑
                        </div>
                        <h3 class="text-4xl font-heading font-extrabold text-slate-800" id="modal-title">Magic Door Keys!</h3>
                        <p class="mt-4 text-lg text-slate-600 font-medium max-w-2xl mx-auto">
                            Pick a character to play as. The playground resets every 24 hours, so don't be afraid to click buttons and test features! 🛠️
                        </p>
                    </div>

                    <!-- Joyful Credentials Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Admin -->
                        <div class="card-liquid p-6 bg-violet-50/50 border-2 border-violet-100 hover:border-violet-400 group">
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-violet-100/50">
                                <span class="text-4xl group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300">👑</span>
                                <h4 class="font-heading text-2xl font-bold text-violet-800">Admin</h4>
                            </div>
                            <div class="space-y-3 font-medium text-slate-700">
                                <div><span class="text-violet-400 text-sm font-bold uppercase tracking-wide">ID</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm text-base">admin@myacademy.local</span></div>
                                <div><span class="text-violet-400 text-sm font-bold uppercase tracking-wide">Secret Pass</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm tracking-widest text-base">password</span></div>
                            </div>
                            <a href="{{ route('login') }}" class="mt-6 block w-full text-center rounded-2xl bg-violet-500 py-3 font-bold text-white shadow-md hover:bg-violet-600 hover:-translate-y-1 transition">Play as Admin</a>
                        </div>

                        <!-- Teacher -->
                        <div class="card-liquid p-6 bg-emerald-50/50 border-2 border-emerald-100 hover:border-emerald-400 group">
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-emerald-100/50">
                                <span class="text-4xl group-hover:scale-125 group-hover:-rotate-12 transition-transform duration-300">👩‍🏫</span>
                                <h4 class="font-heading text-2xl font-bold text-emerald-800">Teacher</h4>
                            </div>
                            <div class="space-y-3 font-medium text-slate-700">
                                <div><span class="text-emerald-400 text-sm font-bold uppercase tracking-wide">ID</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm text-base">teacher@myacademy.local</span></div>
                                <div><span class="text-emerald-400 text-sm font-bold uppercase tracking-wide">Secret Pass</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm tracking-widest text-base">password</span></div>
                            </div>
                            <a href="{{ route('login') }}" class="mt-6 block w-full text-center rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-md hover:bg-emerald-600 hover:-translate-y-1 transition">Play as Teacher</a>
                        </div>

                        <!-- Student -->
                        <div class="card-liquid p-6 bg-sky-50/50 border-2 border-sky-100 hover:border-sky-400 group">
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-sky-100/50">
                                <span class="text-4xl group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300">🎒</span>
                                <h4 class="font-heading text-2xl font-bold text-sky-800">Student</h4>
                            </div>
                            <div class="space-y-3 font-medium text-slate-700">
                                <div><span class="text-sky-400 text-sm font-bold uppercase tracking-wide">Admission No.</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm text-base">ADM-2026-0092</span></div>
                                <div><span class="text-sky-400 text-sm font-bold uppercase tracking-wide">Secret Pass</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm tracking-widest text-base">amina0092</span></div>
                            </div>
                            <a href="{{ route('login') }}" class="mt-6 block w-full text-center rounded-2xl bg-sky-500 py-3 font-bold text-white shadow-md hover:bg-sky-600 hover:-translate-y-1 transition">Play as Student</a>
                        </div>

                        <!-- Parent -->
                        <div class="card-liquid p-6 bg-pink-50/50 border-2 border-pink-100 hover:border-pink-400 group">
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-pink-100/50">
                                <span class="text-4xl group-hover:scale-125 group-hover:-rotate-12 transition-transform duration-300">👨‍👩‍👦</span>
                                <h4 class="font-heading text-2xl font-bold text-pink-800">Parent</h4>
                            </div>
                            <div class="space-y-3 font-medium text-slate-700">
                                <div><span class="text-pink-400 text-sm font-bold uppercase tracking-wide">ID</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm text-base">parent1@myacademy.local</span></div>
                                <div><span class="text-pink-400 text-sm font-bold uppercase tracking-wide">Secret Pass</span> <br/><span class="bg-white px-3 py-1 rounded-xl shadow-sm tracking-widest text-base">password</span></div>
                            </div>
                            <a href="{{ route('login') }}" class="mt-6 block w-full text-center rounded-2xl bg-pink-500 py-3 font-bold text-white shadow-md hover:bg-pink-600 hover:-translate-y-1 transition">Play as Parent</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
