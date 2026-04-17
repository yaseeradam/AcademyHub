<?php

namespace App\Livewire\Marketplace;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductDetail extends Component
{
    public string $product;
    public array $productData;

    public function mount(string $product)
    {
        $this->product = $product;
        $this->productData = $this->getProductData($product);
        
        if (empty($this->productData)) {
            abort(404);
        }
    }

    private function getProductData(string $product): array
    {
        $products = [
            'whatsapp-bot' => [
                'name' => 'WhatsApp Bot',
                'short_description' => 'Automated parent notifications & interactive bot',
                'description' => 'The MyAcademy WhatsApp Bot is a comprehensive automated messaging system that allows parents to receive real-time notifications about their children\'s school activities and interact with the school through WhatsApp.',
                'icon' => 'whatsapp',
                'color' => 'green',
                'price' => 'FREE',
                'category' => 'Communication',
                'rating' => '4.8',
                'downloads' => '1.2k',
                'features' => [
                    'Daily attendance alerts',
                    'Report card notifications',
                    'Fee payment reminders',
                    'Interactive bot commands',
                    'Rich media support',
                    'Automated school announcements'
                ],
                'screenshots' => [
                    'whatsapp-bot-1.png',
                    'whatsapp-bot-2.png',
                    'whatsapp-bot-3.png'
                ],
                'requirements' => [
                    'Dedicated WhatsApp number',
                    'Node.js 18+',
                    'VPS server',
                    'Internet connection'
                ],
                'benefits' => [
                    'Save ₦210,000/month compared to paid APIs',
                    'Instant parent communication',
                    'Reduce phone calls to school by 60%',
                    '99% message delivery rate'
                ]
            ],
            'student-dashboard' => [
                'name' => 'Students/Parents Dashboard',
                'short_description' => 'Complete portal with results & attendance for students and parents',
                'description' => 'A comprehensive portal that provides students and parents with access to academic records, attendance history, results, and school announcements in a user-friendly interface.',
                'icon' => 'student',
                'color' => 'blue',
                'price' => 'FREE',
                'category' => 'Education',
                'rating' => '4.6',
                'downloads' => '890',
                'features' => [
                    'View academic results',
                    'Check attendance records',
                    'Download report cards',
                    'School announcements',
                    'Timetable access',
                    'Profile management'
                ],
                'screenshots' => [
                    'student-dashboard-1.png',
                    'student-dashboard-2.png',
                    'student-dashboard-3.png'
                ],
                'requirements' => [
                    'MyAcademy system',
                    'Student admission number',
                    'Web browser',
                    'Internet connection'
                ],
                'benefits' => [
                    'Students can track their progress',
                    'Reduced administrative workload',
                    'Better parent-student communication',
                    'Mobile-friendly interface'
                ]
            ],
            'cbt' => [
                'name' => 'CBT (Computer-Based Testing)',
                'short_description' => 'Complete examination management system',
                'description' => 'A comprehensive computer-based testing system that allows schools to create, manage, and conduct online examinations with automatic grading, analytics, and detailed reporting.',
                'icon' => 'exam',
                'color' => 'purple',
                'price' => 'FREE',
                'category' => 'Examination',
                'rating' => '4.7',
                'downloads' => '650',
                'features' => [
                    'Create multiple choice questions',
                    'Automatic grading system',
                    'Timed examinations',
                    'Question bank management',
                    'Student performance analytics',
                    'Exam scheduling'
                ],
                'screenshots' => [
                    'cbt-1.png',
                    'cbt-2.png',
                    'cbt-3.png'
                ],
                'requirements' => [
                    'MyAcademy system',
                    'Web browser',
                    'Stable internet connection',
                    'Computer lab (recommended)'
                ],
                'benefits' => [
                    'Eliminate paper-based exams',
                    'Instant result processing',
                    'Reduce examination malpractice',
                    'Save time on grading'
                ]
            ],
            'savings-loan' => [
                'name' => 'Savings & Loan Management',
                'short_description' => 'Staff financial management module',
                'description' => 'A complete financial management system for staff savings and loan programs. Track contributions, manage loan applications, calculate interest, and generate financial reports.',
                'icon' => 'finance',
                'color' => 'emerald',
                'price' => 'FREE',
                'category' => 'Finance',
                'rating' => '4.5',
                'downloads' => '420',
                'features' => [
                    'Staff savings tracking',
                    'Loan application management',
                    'Interest calculation',
                    'Payment schedules',
                    'Financial reports',
                    'Automated deductions'
                ],
                'screenshots' => [
                    'savings-loan-1.png',
                    'savings-loan-2.png',
                    'savings-loan-3.png'
                ],
                'requirements' => [
                    'MyAcademy system',
                    'Admin access',
                    'Staff records',
                    'Payroll integration'
                ],
                'benefits' => [
                    'Improve staff welfare',
                    'Transparent financial tracking',
                    'Automated loan calculations',
                    'Reduce manual paperwork'
                ]
            ],
            'agent-pro' => [
                'name' => 'AgentPro AI',
                'short_description' => 'Intelligent AI assistant platform integration',
                'description' => 'AgentPro AI is a revolutionary contextual AI assistant built for schools. It directly connects with student datasets, tracking attendance, results, fees, and more to answer inquiries intelligently and warmly like a live school administrator.',
                'icon' => 'robot',
                'color' => 'pink',
                'price' => 'FREE',
                'category' => 'AI',
                'rating' => '5.0',
                'downloads' => '4.1k',
                'features' => [
                    'Natural language queries',
                    'Student performance analysis',
                    'Attendance tracking',
                    'Fee payment status',
                    'Warm human-like personality',
                    'Real-time database integration'
                ],
                'screenshots' => [
                    'agentpro-1.png',
                    'agentpro-2.png',
                    'agentpro-3.png'
                ],
                'requirements' => [
                    'MyAcademy system',
                    'Internet connection',
                    'API access configuration'
                ],
                'benefits' => [
                    'Instant answers 24/7',
                    'Reduces administrative burden',
                    'Highly accurate database reads',
                    'Zero-learning curve chat interface'
                ]
            ],
            'homework' => [
                'name' => 'Homework & Assignments',
                'short_description' => 'Manage and track student assignments',
                'description' => 'A complete assignment creation and submission platform. Teachers can generate homework with AI, assign it to classes, and students can submit their work directly from the portal for grading.',
                'icon' => 'document',
                'color' => 'cyan',
                'price' => 'FREE',
                'category' => 'Education',
                'rating' => '4.8',
                'downloads' => '2.8k',
                'features' => [
                    'AI-powered assignment generation',
                    'Due date enforcement',
                    'Digital submissions handling',
                    'Inline grading & feedback',
                    'Push notifications for students',
                    'Class/Section filtering'
                ],
                'screenshots' => [
                    'homework-1.png',
                    'homework-2.png',
                    'homework-3.png'
                ],
                'requirements' => [
                    'MyAcademy system',
                    'Active Student & Teacher accounts'
                ],
                'benefits' => [
                    'Streamlined assignment workflow',
                    'Automated due date tracking',
                    'Easy submission management',
                    'Built-in AI content generation'
                ]
            ]
        ];

        return $products[$product] ?? [];
    }

    public function getTitle(): string
    {
        return $this->productData['name'] ?? 'Product';
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        return view('livewire.marketplace.product-detail')
            ->title($this->getTitle());
    }
}