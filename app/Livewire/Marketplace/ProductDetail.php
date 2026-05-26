<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceComponent;
use App\Models\PluginReview;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ProductDetail extends Component
{
    use WithFileUploads;

    public string $product;
    public array $productData;

    // New billing & class targeting state
    public array $selectedClasses = [];
    public float $setupFee = 0;
    public float $usageFeePerStudent = 0;
    public int $calculatedStudentCount = 0;
    public float $estimatedTermlyUsageFee = 0;

    // Review form
    public int $reviewRating = 5;
    public string $reviewComment = '';
    public bool $showReviewForm = false;

    // Uninstall
    public bool $confirmingUninstall = false;

    // Install Preview Modal
    public bool $showInstallPreviewModal = false;

    // Super Admin Control Properties
    public float $adminPrice = 0.00;
    public float $adminSetupFee = 0.00;
    public float $adminUsageFeePerStudent = 0.00;
    public bool $adminIsActive = true;
    public int $adminScreenshotCount = 3;
    public array $adminScreenshotsMetadata = [];
    public string $adminIcon = '';
    public bool $confirmingDeleteComponent = false;

    // Temporary upload files
    public $screenshotFiles = [];
    public $iconFile;

    public function mount(string $product)
    {
        $this->product = $product;
        $this->productData = $this->getProductData($product);
        if (empty($this->productData)) {
            abort(404);
        }

        $dbComponent = $this->getDbComponent();
        if ($dbComponent) {
            $this->adminPrice = (float) $dbComponent->price;
            $this->adminSetupFee = (float) $dbComponent->setup_fee;
            $this->adminUsageFeePerStudent = (float) $dbComponent->usage_fee_per_student;
            $this->adminIsActive = (bool) $dbComponent->is_active;
            $this->adminScreenshotCount = (int) ($dbComponent->screenshot_count ?? 3);
            $this->adminIcon = $dbComponent->icon ?? '';
            
            $metadata = $dbComponent->screenshots_metadata;
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true) ?: [];
            }
            $this->adminScreenshotsMetadata = is_array($metadata) ? $metadata : [];

            // Populate metadata arrays with structured data containing filename and title
            for ($i = 0; $i < 5; $i++) {
                if (!isset($this->adminScreenshotsMetadata[$i]) || !is_array($this->adminScreenshotsMetadata[$i])) {
                    $oldTitle = is_string($this->adminScreenshotsMetadata[$i] ?? null) 
                        ? $this->adminScreenshotsMetadata[$i] 
                        : ('Screenshot ' . ($i + 1));
                        
                    $defaultFilename = '';
                    if (isset($this->productData['screenshots'][$i])) {
                        $defaultFilename = $this->productData['screenshots'][$i];
                    } else {
                        $defaultFilename = $this->product . '-' . ($i + 1) . '.png';
                    }

                    $this->adminScreenshotsMetadata[$i] = [
                        'filename' => $defaultFilename,
                        'title' => $oldTitle,
                    ];
                }
            }

            $user = auth()->user();
            if ($user && $user->tenant) {
                $pivot = $user->tenant->marketplaceComponents()
                    ->where('marketplace_component_id', $dbComponent->id)
                    ->wherePivotNotNull('installed_at')
                    ->wherePivotNull('uninstalled_at')
                    ->first();
                if ($pivot && $pivot->pivot) {
                    $rawClasses = $pivot->pivot->allowed_class_ids ?? [];
                    $this->selectedClasses = is_string($rawClasses)
                        ? (json_decode($rawClasses, true) ?: [])
                        : (is_array($rawClasses) ? $rawClasses : []);
                }
            }
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
                'category' => 'Communication',
                'features' => ['Daily attendance alerts', 'Report card notifications', 'Fee payment reminders', 'Interactive bot commands', 'Rich media support', 'Automated school announcements'],
                'screenshots' => ['whatsapp-bot-1.png', 'whatsapp-bot-2.png', 'whatsapp-bot-3.png'],
                'requirements' => ['Dedicated WhatsApp number', 'Node.js 18+', 'VPS server', 'Internet connection'],
                'benefits' => ['Save ₦210,000/month compared to paid APIs', 'Instant parent communication', 'Reduce phone calls to school by 60%', '99% message delivery rate'],
            ],
            'student-dashboard' => [
                'name' => 'Students/Parents Dashboard',
                'short_description' => 'Complete portal with results & attendance for students and parents',
                'description' => 'A comprehensive portal that provides students and parents with access to academic records, attendance history, results, and school announcements in a user-friendly interface.',
                'icon' => 'student',
                'color' => 'blue',
                'category' => 'Education',
                'features' => ['View academic results', 'Check attendance records', 'Download report cards', 'School announcements', 'Timetable access', 'Profile management'],
                'screenshots' => ['student-dashboard-1.png', 'student-dashboard-2.png', 'student-dashboard-3.png'],
                'requirements' => ['MyAcademy system', 'Student admission number', 'Web browser', 'Internet connection'],
                'benefits' => ['Students can track their progress', 'Reduced administrative workload', 'Better parent-student communication', 'Mobile-friendly interface'],
            ],
            'cbt' => [
                'name' => 'CBT (Computer-Based Testing)',
                'short_description' => 'Complete examination management system',
                'description' => 'A comprehensive computer-based testing system that allows schools to create, manage, and conduct online examinations with automatic grading, analytics, and detailed reporting.',
                'icon' => 'exam',
                'color' => 'purple',
                'category' => 'Examination',
                'features' => ['Create multiple choice questions', 'Automatic grading system', 'Timed examinations', 'Question bank management', 'Student performance analytics', 'Exam scheduling'],
                'screenshots' => ['cbt-1.png', 'cbt-2.png', 'cbt-3.png'],
                'requirements' => ['MyAcademy system', 'Web browser', 'Stable internet connection', 'Computer lab (recommended)'],
                'benefits' => ['Eliminate paper-based exams', 'Instant result processing', 'Reduce examination malpractice', 'Save time on grading'],
            ],
            'savings-loan' => [
                'name' => 'Savings & Loan Management',
                'short_description' => 'Staff financial management module',
                'description' => 'A complete financial management system for staff savings and loan programs. Track contributions, manage loan applications, calculate interest, and generate financial reports.',
                'icon' => 'finance',
                'color' => 'emerald',
                'category' => 'Finance',
                'features' => ['Staff savings tracking', 'Loan application management', 'Interest calculation', 'Payment schedules', 'Financial reports', 'Automated deductions'],
                'screenshots' => ['savings-loan-1.png', 'savings-loan-2.png', 'savings-loan-3.png'],
                'requirements' => ['MyAcademy system', 'Admin access', 'Staff records', 'Payroll integration'],
                'benefits' => ['Improve staff welfare', 'Transparent financial tracking', 'Automated loan calculations', 'Reduce manual paperwork'],
            ],
            'messages' => [
                'name' => 'Internal Messaging',
                'short_description' => 'Real-time staff & admin communication system',
                'description' => 'A built-in messaging system that lets admins, teachers, and bursars communicate directly within MyAcademy.',
                'icon' => 'messages',
                'color' => 'amber',
                'category' => 'Communication',
                'features' => ['Direct one-on-one messaging', 'File & document attachments', 'Unread message badge', 'Real-time polling', 'Role-based recipient filtering', 'Message history'],
                'screenshots' => ['messages-1.png', 'messages-2.png', 'messages-3.png'],
                'requirements' => ['MyAcademy system', 'Admin, Teacher, or Bursar account', 'Web browser'],
                'benefits' => ['Eliminate external messaging apps', 'Keep all communication in-system', 'Instant file sharing between staff', 'Full message audit trail'],
            ],
            'homework' => [
                'name' => 'Homework & Assignments',
                'short_description' => 'Manage and track student assignments',
                'description' => 'A complete assignment creation and submission platform. Teachers can generate homework with AI, assign it to classes, and students can submit their work directly from the portal.',
                'icon' => 'document',
                'color' => 'cyan',
                'category' => 'Education',
                'features' => ['AI-powered assignment generation', 'Due date enforcement', 'Digital submissions handling', 'Inline grading & feedback', 'Push notifications for students', 'Class/Section filtering'],
                'screenshots' => ['homework-1.png', 'homework-2.png', 'homework-3.png'],
                'requirements' => ['MyAcademy system', 'Active Student & Teacher accounts'],
                'benefits' => ['Streamlined assignment workflow', 'Automated due date tracking', 'Easy submission management', 'Built-in AI content generation'],
            ],
            'e-learning' => [
                'name' => 'E-Learning',
                'short_description' => 'Digital learning resources and class notes',
                'description' => 'The MyAcademy E-Learning module allows teachers to upload digital learning resources, class notes, and course materials, allowing students to access and study course content online.',
                'icon' => 'document',
                'color' => 'cyan',
                'category' => 'Education',
                'features' => ['Class note management', 'Document sharing', 'Offline study access', 'Organized by subjects & terms', 'Student portal integration', 'Admin oversight'],
                'screenshots' => ['e-learning-1.png', 'e-learning-2.png', 'e-learning-3.png'],
                'requirements' => ['MyAcademy system', 'Web browser', 'Internet connection'],
                'benefits' => ['Eliminate paper handouts', 'Centralized study materials', 'Accessible anytime, anywhere', 'Improve student preparation'],
            ],
            'parent-portal' => [
                'name' => 'Parent Portal',
                'short_description' => 'Dedicated access for parents to monitor academic performance & bills',
                'description' => 'The MyAcademy Parent Portal is a dedicated space for parents to log in securely and check their children\'s real-time progress, attendance logs, exam scores, and unpaid/paid financial bills.',
                'icon' => 'student',
                'color' => 'blue',
                'category' => 'Portal',
                'features' => ['Child academic tracking', 'Detailed report cards', 'Unpaid fee summary', 'Real-time school announcements', 'Direct messaging to admin/teachers', 'Attendance log overview'],
                'screenshots' => ['parent-portal-1.png', 'parent-portal-2.png', 'parent-portal-3.png'],
                'requirements' => ['MyAcademy system', 'Web browser', 'Active parent credentials'],
                'benefits' => ['Instant parent engagement', 'Streamlined bill payments tracking', 'Reduce physical report card printing costs', 'Build stronger school-parent trust'],
            ],
        ];

        return $products[$product] ?? [];
    }

    public function getTitle(): string
    {
        return $this->productData['name'] ?? 'Product';
    }

    /** Check if this tenant has the plugin installed (and not uninstalled) */
    private function getDbComponent(): ?MarketplaceComponent
    {
        return MarketplaceComponent::where('slug', $this->product)->first();
    }

    public function submitReview(): void
    {
        $this->validate([
            'reviewRating'  => 'required|integer|min:1|max:5',
            'reviewComment' => 'nullable|string|max:500',
        ]);

        PluginReview::updateOrCreate(
            ['user_id' => auth()->id(), 'component_slug' => $this->product],
            ['rating' => $this->reviewRating, 'comment' => $this->reviewComment]
        );

        // Update average on the component record
        $component = $this->getDbComponent();
        if ($component) {
            $avg = PluginReview::where('component_slug', $this->product)->avg('rating');
            $cnt = PluginReview::where('component_slug', $this->product)->count();
            $component->update(['rating_avg' => round($avg, 2), 'rating_count' => $cnt]);
        }

        $this->showReviewForm = false;
        $this->reviewComment = '';
        session()->flash('review_success', 'Thank you for your review!');
    }

    public function startUninstall(): void
    {
        $this->confirmingUninstall = true;
    }

    public function cancelUninstall(): void
    {
        $this->confirmingUninstall = false;
    }

    public function uninstall(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $tenant = $user->tenant;
        if (!$tenant) return;

        $component = $this->getDbComponent();
        if ($component) {
            // Soft-uninstall: set uninstalled_at on pivot
            $tenant->marketplaceComponents()
                ->wherePivot('marketplace_component_id', $component->id)
                ->updateExistingPivot($component->id, [
                    'uninstalled_at' => now(),
                ]);

            // Decrement install count if greater than 0
            if ($component->installs > 0) {
                $component->decrement('installs');
            }

            // Audit log
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => 'plugin_uninstalled',
                'model'   => 'MarketplaceComponent',
                'model_id'=> $component->id,
                'changes' => json_encode(['slug' => $this->product, 'uninstalled_at' => now()]),
            ]);
        }        $this->confirmingUninstall = false;
        session()->flash('message', 'Plugin uninstalled successfully. You can reinstall it from the marketplace.');
        $this->redirect(route('marketplace'), navigate: false);
    }

    public function previewInstall(): void
    {
        if (empty($this->selectedClasses)) {
            session()->flash('message_error', 'Please select at least one class before installing.');
            return;
        }
        $this->showInstallPreviewModal = true;
    }

    public function cancelInstallPreview(): void
    {
        $this->showInstallPreviewModal = false;
    }

    public function install(): void
    {
        $this->showInstallPreviewModal = false;
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $tenant = $user->tenant;
        abort_unless($tenant, 404);

        $dbComponent = $this->getDbComponent();
        abort_unless($dbComponent, 404);

        $setupFee = (float) $dbComponent->setup_fee;
        $usageFee = (float) $dbComponent->usage_fee_per_student;

        // Soft install or sync
        $tenant->marketplaceComponents()->syncWithoutDetaching([
            $dbComponent->id => [
                'installed_at'             => now(),
                'uninstalled_at'           => null,
                'status'                   => 'active',
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => $usageFee,
                'price_paid'               => $setupFee,
                'student_count_at_install' => $this->calculatedStudentCount,
                'allowed_class_ids'        => $this->selectedClasses,
            ]
        ]);

        // If it was already in pivot, update existing pivot fields directly to make sure uninstalled_at is reset
        $tenant->marketplaceComponents()->updateExistingPivot($dbComponent->id, [
            'installed_at'             => now(),
            'uninstalled_at'           => null,
            'status'                   => 'active',
            'setup_fee'                => $setupFee,
            'usage_fee_per_student'    => $usageFee,
            'price_paid'               => $setupFee,
            'student_count_at_install' => $this->calculatedStudentCount,
            'allowed_class_ids'        => $this->selectedClasses,
        ]);

        // Increment install count on component
        $dbComponent->increment('installs');

        // Automatically create unpaid Setup Fee bill in the ledger
        if ($setupFee > 0) {
            \App\Models\TenantPluginBill::create([
                'tenant_id'                => $tenant->id,
                'marketplace_component_id' => $dbComponent->id,
                'bill_type'                => 'setup',
                'term_name'                => null,
                'session_name'             => null,
                'student_count'            => null,
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => 0,
                'total_due'                => $setupFee,
                'status'                   => 'unpaid',
            ]);
        }

        // Automatically create unpaid Termly Usage bill in the ledger if usage fee is configured
        if ($usageFee > 0 && $this->calculatedStudentCount > 0) {
            $activeTerm = \App\Models\AcademicTerm::active();
            $termName = $activeTerm?->name ?? 'First Term';
            $sessionName = \App\Models\AcademicTerm::activeSessionName() ?? '2026/2027';
            $totalUsageDue = $usageFee * $this->calculatedStudentCount;

            \App\Models\TenantPluginBill::create([
                'tenant_id'                => $tenant->id,
                'marketplace_component_id' => $dbComponent->id,
                'bill_type'                => 'usage',
                'term_name'                => $termName,
                'session_name'             => $sessionName,
                'student_count'            => $this->calculatedStudentCount,
                'setup_fee'                => 0,
                'usage_fee_per_student'    => $usageFee,
                'total_due'                => $totalUsageDue,
                'status'                   => 'unpaid',
            ]);
        }

        // Audit log
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => 'plugin_installed',
            'model'   => 'MarketplaceComponent',
            'model_id'=> $dbComponent->id,
            'changes' => json_encode([
                'slug'              => $this->product,
                'allowed_class_ids' => $this->selectedClasses,
                'setup_fee'         => $setupFee,
                'usage_fee'         => $usageFee,
            ]),
        ]);

        session()->flash('message', 'Plugin installed successfully!');
        $this->redirect(route('marketplace'), navigate: false);
    }

    public function updateClasses(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $tenant = $user->tenant;
        abort_unless($tenant, 404);

        $dbComponent = $this->getDbComponent();
        abort_unless($dbComponent, 404);

        // Update classes
        $tenant->marketplaceComponents()
            ->updateExistingPivot($dbComponent->id, [
                'allowed_class_ids' => $this->selectedClasses,
            ]);

        // Audit log
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => 'plugin_classes_updated',
            'model'   => 'MarketplaceComponent',
            'model_id'=> $dbComponent->id,
            'changes' => json_encode([
                'slug'              => $this->product,
                'allowed_class_ids' => $this->selectedClasses,
            ]),
        ]);

        session()->flash('message', 'Plugin active classes updated successfully!');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $tenant  = $user->tenant;
        $isInstalled = false;
        $installPivot = null;
        $dbComponent  = $this->getDbComponent();

        if ($dbComponent) {
            $this->setupFee = (float) $dbComponent->setup_fee;
            $this->usageFeePerStudent = (float) $dbComponent->usage_fee_per_student;

            if ($tenant) {
                $pivot = $tenant->marketplaceComponents()
                    ->where('marketplace_component_id', $dbComponent->id)
                    ->wherePivotNotNull('installed_at')
                    ->wherePivotNull('uninstalled_at')
                    ->first();
                $isInstalled = $pivot !== null;
                $installPivot = $pivot?->pivot;

                if ($pivot && $pivot->pivot) {
                    if ($pivot->pivot->setup_fee !== null) {
                        $this->setupFee = (float) $pivot->pivot->setup_fee;
                    }
                    if ($pivot->pivot->usage_fee_per_student !== null) {
                        $this->usageFeePerStudent = (float) $pivot->pivot->usage_fee_per_student;
                    }
                }
            }
        }

        // Load active classes with student counts
        $classes = \App\Models\SchoolClass::withCount(['students' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('level')->orderBy('name')->get();

        // Recalculate dynamic student count based on class selection
        if (!empty($this->selectedClasses)) {
            $this->calculatedStudentCount = Student::whereIn('class_id', $this->selectedClasses)
                ->where('status', 'active')
                ->count();
        } else {
            $this->calculatedStudentCount = 0;
        }

        $this->estimatedTermlyUsageFee = $this->usageFeePerStudent * $this->calculatedStudentCount;

        $ratingAvg  = $dbComponent?->real_rating_avg ?? 0;
        $ratingCount = $dbComponent?->real_rating_count ?? 0;
        $reviews      = PluginReview::where('component_slug', $this->product)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();
        $userReview   = PluginReview::where('component_slug', $this->product)
            ->where('user_id', auth()->id())
            ->first();

        return view('livewire.marketplace.product-detail', [
            'isInstalled'   => $isInstalled,
            'installPivot'  => $installPivot,
            'classes'       => $classes,
            'dbComponent'   => $dbComponent,
            'ratingAvg'     => $ratingAvg,
            'ratingCount'   => $ratingCount,
            'reviews'       => $reviews,
            'userReview'    => $userReview
        ])->title($this->getTitle());
    }

    public function updatedScreenshotFiles($value, $key)
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        $index = (int) $key;
        $file = $this->screenshotFiles[$index] ?? null;
        if ($file) {
            $extension = $file->getClientOriginalExtension();
            $filename = $this->product . '-screenshot-' . ($index + 1) . '-' . time() . '.' . $extension;
            
            // Move directly to public/images
            $destinationPath = public_path('images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);
            
            // Ensure metadata array has the correct structure for the index
            if (!isset($this->adminScreenshotsMetadata[$index]) || !is_array($this->adminScreenshotsMetadata[$index])) {
                $this->adminScreenshotsMetadata[$index] = [
                    'filename' => $filename,
                    'title' => 'Screenshot ' . ($index + 1),
                ];
            } else {
                $this->adminScreenshotsMetadata[$index]['filename'] = $filename;
            }
            
            session()->flash('superadmin_success', 'Screenshot ' . ($index + 1) . ' uploaded successfully!');
        }
    }

    public function updatedIconFile()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        if ($this->iconFile) {
            $extension = $this->iconFile->getClientOriginalExtension();
            if (strtolower($extension) === 'svg') {
                $content = file_get_contents($this->iconFile->getRealPath());
                $this->adminIcon = $content;
                session()->flash('superadmin_success', 'SVG Icon parsed successfully! Click Save Config to persist.');
            } else {
                session()->flash('superadmin_error', 'Only SVG files are supported for icon uploads.');
            }
        }
    }

    public function saveSuperAdminSettings(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $dbComponent = $this->getDbComponent();
        abort_unless($dbComponent, 404);

        $dbComponent->update([
            'price'                 => $this->adminPrice,
            'setup_fee'             => $this->adminSetupFee,
            'usage_fee_per_student' => $this->adminUsageFeePerStudent,
            'is_active'             => $this->adminIsActive,
            'screenshot_count'      => $this->adminScreenshotCount,
            'screenshots_metadata'  => $this->adminScreenshotsMetadata,
            'icon'                  => $this->adminIcon,
        ]);

        session()->flash('superadmin_success', 'Component settings saved successfully!');
    }

    public function startDeleteComponent(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);
        $this->confirmingDeleteComponent = true;
    }

    public function cancelDeleteComponent(): void
    {
        $this->confirmingDeleteComponent = false;
    }

    public function deleteComponentEntirely(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $dbComponent = $this->getDbComponent();
        if ($dbComponent) {
            $dbComponent->delete();
        }

        session()->flash('message', 'Component removed entirely from the marketplace.');
        $this->redirect(route('marketplace'), navigate: false);
    }

    public function enableInstantlyForSchool(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $user = auth()->user();
        $tenant = $user->tenant;
        abort_unless($tenant, 404);

        $dbComponent = $this->getDbComponent();
        abort_unless($dbComponent, 404);

        $setupFee = 0.00; 
        $usageFee = 0.00; 

        $tenant->marketplaceComponents()->syncWithoutDetaching([
            $dbComponent->id => [
                'installed_at'             => now(),
                'uninstalled_at'           => null,
                'status'                   => 'active',
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => $usageFee,
                'price_paid'               => 0.00,
                'student_count_at_install' => 0,
                'allowed_class_ids'        => [],
            ]
        ]);

        $tenant->marketplaceComponents()->updateExistingPivot($dbComponent->id, [
            'installed_at'             => now(),
            'uninstalled_at'           => null,
            'status'                   => 'active',
            'setup_fee'                => $setupFee,
            'usage_fee_per_student'    => $usageFee,
            'price_paid'               => 0.00,
            'student_count_at_install' => 0,
        ]);

        $dbComponent->increment('installs');

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => 'plugin_installed_instantly_by_superadmin',
            'model'   => 'MarketplaceComponent',
            'model_id'=> $dbComponent->id,
            'changes' => json_encode([
                'slug'              => $this->product,
                'setup_fee'         => 0,
                'usage_fee'         => 0,
            ]),
        ]);

        session()->flash('message', 'Plugin enabled instantly for this school by Super Admin!');
        $this->redirect(route('marketplace'), navigate: false);
    }
}
