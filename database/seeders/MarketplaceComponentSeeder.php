<?php

namespace Database\Seeders;

use App\Models\MarketplaceComponent;
use Illuminate\Database\Seeder;

class MarketplaceComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            [
                'name' => 'WhatsApp Bot',
                'slug' => 'whatsapp-bot',
                'route_name' => null,
                'price' => 0.00,
                'pricing_model' => 'flat',
                'setup_fee' => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Automated parent notifications & interactive bot',
                'description' => 'The MyAcademy WhatsApp Bot is a comprehensive automated messaging system that allows parents to receive real-time notifications about their children\'s school activities and interact with the school through WhatsApp.',
                'category' => 'Communication',
                'icon' => 'whatsapp',
                'is_active' => true,
                'rating_avg' => 4.80,
                'rating_count' => 12,
                'installs' => 15,
            ],
            [
                'name' => 'Students/Parents Dashboard',
                'slug' => 'student-dashboard',
                'route_name' => 'student.dashboard',
                'price' => 0.00,
                'pricing_model' => 'flat',
                'setup_fee' => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Complete portal with results & attendance for students and parents',
                'description' => 'A comprehensive portal that provides students and parents with access to academic records, attendance history, results, and school announcements in a user-friendly interface.',
                'category' => 'Education',
                'icon' => 'student',
                'is_active' => true,
                'rating_avg' => 4.90,
                'rating_count' => 24,
                'installs' => 45,
            ],
            [
                'name' => 'CBT (Computer-Based Testing)',
                'slug' => 'cbt',
                'route_name' => 'cbt.student',
                'price' => 15000.00,
                'pricing_model' => 'flat',
                'setup_fee' => 10000.00,
                'usage_fee_per_student' => 150.00,
                'short_description' => 'Complete examination management system',
                'description' => 'A comprehensive computer-based testing system that allows schools to create, manage, and conduct online examinations with automatic grading, analytics, and detailed reporting.',
                'category' => 'Examination',
                'icon' => 'exam',
                'is_active' => true,
                'rating_avg' => 4.75,
                'rating_count' => 18,
                'installs' => 22,
            ],
            [
                'name' => 'Savings & Loan Management',
                'slug' => 'savings-loan',
                'route_name' => null,
                'price' => 25000.00,
                'pricing_model' => 'flat',
                'setup_fee' => 5000.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Staff financial management module',
                'description' => 'A complete financial management system for staff savings and loan programs. Track contributions, manage loan applications, calculate interest, and generate financial reports.',
                'category' => 'Finance',
                'icon' => 'finance',
                'is_active' => true,
                'rating_avg' => 4.60,
                'rating_count' => 8,
                'installs' => 10,
            ],
            [
                'name' => 'Internal Messaging',
                'slug' => 'messages',
                'route_name' => null,
                'price' => 0.00,
                'pricing_model' => 'flat',
                'setup_fee' => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Real-time staff & admin communication system',
                'description' => 'A built-in messaging system that lets admins, teachers, and bursars communicate directly within MyAcademy.',
                'category' => 'Communication',
                'icon' => 'messages',
                'is_active' => true,
                'rating_avg' => 4.70,
                'rating_count' => 14,
                'installs' => 29,
            ],
            [
                'name' => 'Homework & Assignments',
                'slug' => 'homework',
                'route_name' => 'student.homework',
                'price' => 0.00,
                'pricing_model' => 'flat',
                'setup_fee' => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Manage and track student assignments',
                'description' => 'A complete assignment creation and submission platform. Teachers can generate homework with AI, assign it to classes, and students can submit their work directly from the portal.',
                'category' => 'Education',
                'icon' => 'document',
                'is_active' => true,
                'rating_avg' => 4.85,
                'rating_count' => 31,
                'installs' => 52,
            ],
            [
                'name' => 'E-Learning',
                'slug' => 'e-learning',
                'route_name' => null,
                'price' => 10000.00,
                'pricing_model' => 'flat',
                'setup_fee' => 5000.00,
                'usage_fee_per_student' => 50.00,
                'short_description' => 'Digital learning resources and class notes',
                'description' => 'The MyAcademy E-Learning module allows teachers to upload digital learning resources, class notes, and course materials, allowing students to access and study course content online.',
                'category' => 'Education',
                'icon' => 'document',
                'is_active' => true,
                'rating_avg' => 4.80,
                'rating_count' => 19,
                'installs' => 34,
            ],
            [
                'name' => 'Parent Portal',
                'slug' => 'parent-portal',
                'route_name' => 'parent-portal.index',
                'price' => 0.00,
                'pricing_model' => 'flat',
                'setup_fee' => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description' => 'Dedicated access for parents to monitor academic performance & bills',
                'description' => 'The MyAcademy Parent Portal is a dedicated space for parents to log in securely and check their children\'s real-time progress, attendance logs, exam scores, and unpaid/paid financial bills.',
                'category' => 'Portal',
                'icon' => 'student',
                'is_active' => true,
                'rating_avg' => 4.95,
                'rating_count' => 42,
                'installs' => 88,
            ],
        ];

        foreach ($components as $component) {
            MarketplaceComponent::updateOrCreate(
                ['slug' => $component['slug']],
                $component
            );
        }
    }
}
