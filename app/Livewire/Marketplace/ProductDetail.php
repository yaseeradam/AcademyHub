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
                'name' => 'Student Dashboard',
                'short_description' => 'Complete student portal with results & attendance',
                'description' => 'A comprehensive student portal that provides students with access to their academic records, attendance history, results, and school announcements in a user-friendly interface.',
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