<?php $__env->startSection('content'); ?>
    <?php
        $hasPremium = true;

        $certificateTemplate = old('certificate_template', config('myacademy.certificate_template', 'modern'));
        $reportCardTemplate = old('report_card_template', config('myacademy.report_card_template', 'standard'));

        // Report card options
        $rcShowPosition = old('rc_show_position', config('myacademy.rc_show_position', true));
        $rcShowAttendance = old('rc_show_attendance', config('myacademy.rc_show_attendance', true));
        $rcShowGradingKey = old('rc_show_grading_key', config('myacademy.rc_show_grading_key', true));
        $rcShowClassAverage = old('rc_show_class_average', config('myacademy.rc_show_class_average', true));
        $rcShowWatermark = old('rc_show_watermark', config('myacademy.rc_show_watermark', true));
        $rcShowNextTermDate = old('rc_show_next_term_date', config('myacademy.rc_show_next_term_date', true));
        $rcShowTeacherRemarks = old('rc_show_teacher_remarks', config('myacademy.rc_show_teacher_remarks', true));
        $rcShowPrincipalRemarks = old('rc_show_principal_remarks', config('myacademy.rc_show_principal_remarks', true));
        $rcShowPsychomotor = old('rc_show_psychomotor', config('myacademy.rc_show_psychomotor', false));
        $rcShowSchoolFees = old('rc_show_school_fees', config('myacademy.rc_show_school_fees', false));
        

        $rcSchoolFeesAccountNumber = old('rc_school_fees_account_number', config('myacademy.rc_school_fees_account_number'));
        $rcSchoolFeesBankName = old('rc_school_fees_bank_name', config('myacademy.rc_school_fees_bank_name'));
        $rcSchoolFeesAccountName = old('rc_school_fees_account_name', config('myacademy.rc_school_fees_account_name'));
        $rcSchoolFeesByClass = config('myacademy.rc_school_fees_by_class', []);
        if (is_string($rcSchoolFeesByClass)) {
            $rcSchoolFeesByClass = json_decode($rcSchoolFeesByClass, true) ?? [];
        }
        $rcShowSignatures = old('rc_show_signatures', config('myacademy.rc_show_signatures', false));
        $rcPrincipalSignatureImage = config('myacademy.rc_principal_signature_image');
        $rcTeacherSignatureImage = config('myacademy.rc_teacher_signature_image');
    ?>

    <div class="space-y-6"
        x-data="{
            open: false,
            src: null,
            title: '',
            selectedReportTemplate: '<?php echo e($reportCardTemplate); ?>',
            showSchoolFees: <?php echo e($rcShowSchoolFees ? 'true' : 'false'); ?>,
            showSignatures: <?php echo e($rcShowSignatures ? 'true' : 'false'); ?>,
        }">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Report Card Settings','subtitle' => 'Choose template and customize what appears on the report card.','accent' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Report Card Settings','subtitle' => 'Choose template and customize what appears on the report card.','accent' => 'settings']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

        <div class="flex gap-2">
            <a href="<?php echo e(route('settings.index')); ?>" class="btn-outline">← Back to Settings</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="card-padded border border-orange-200 bg-orange-50/60">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('settings.update-templates')); ?>" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-emerald-50 to-sky-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Report Card Templates</div>
                        <div class="text-sm font-semibold text-gray-600">Choose your preferred report card design - all
                            templates are free!</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <?php
                        $reportTemplates = [
                            [
                                'key' => 'standard',
                                'title' => 'Standard',
                                'desc' => 'Warm amber brand with gradient stats, color-coded grades, and double border frame.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'standard']),
                                'free' => true,
                            ],
                            [
                                'key' => 'compact',
                                'title' => 'Compact',
                                'desc' => 'Clean, minimal layout focused on scores and summary.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'compact']),
                                'free' => true,
                            ],
                            [
                                'key' => 'elegant',
                                'title' => 'Elegant',
                                'desc' => 'Formal navy and gold theme with ornate borders and refined typography.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'elegant']),
                                'free' => true,
                            ],
                            [
                                'key' => 'modern',
                                'title' => 'Modern',
                                'desc' => 'Bold dark mode design with cyan accents and card-based layout.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'modern']),
                                'free' => true,
                            ],
                            [
                                'key' => 'classic',
                                'title' => 'Classic',
                                'desc' => 'Traditional black and white formal layout with maximum readability.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'classic']),
                                'free' => true,
                            ],
                            [
                                'key' => 'vibrant',
                                'title' => 'Vibrant',
                                'desc' => 'Colorful purple and pink gradients with playful modern design.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'vibrant']),
                                'free' => true,
                            ],
                            [
                                'key' => 'professional',
                                'title' => 'Professional',
                                'desc' => 'Corporate blue theme with clean lines and business-like presentation.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'professional']),
                                'free' => true,
                            ],
                            [
                                'key' => 'royal',
                                'title' => 'Royal',
                                'desc' => 'Luxurious purple and gold with ornate decorative elements.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'royal']),
                                'free' => true,
                            ],
                            [
                                'key' => 'fresh',
                                'title' => 'Fresh',
                                'desc' => 'Bright green and teal with nature-inspired clean aesthetics.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'fresh']),
                                'free' => true,
                            ],
                            [
                                'key' => 'sunset',
                                'title' => 'Sunset',
                                'desc' => 'Warm orange and red gradients with energetic modern vibe.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'sunset']),
                                'free' => true,
                            ],
                        ];
                    ?>


                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isLocked = !$t['free'] && !$hasPremium;
                        ?>
                        <label
                            :class="selectedReportTemplate === '<?php echo e($t['key']); ?>' ? 'border-emerald-300 ring-2 ring-emerald-600 bg-emerald-50/10' : 'border-gray-100'"
                            class="group cursor-pointer rounded-3xl border bg-white/70 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur transition hover:shadow-md hover:border-emerald-200 <?php echo e($isLocked ? 'opacity-60' : ''); ?> flex flex-col h-full">
                            <input type="radio" name="report_card_template" value="<?php echo e($t['key']); ?>" class="sr-only" x-model="selectedReportTemplate"
                                <?php if($reportCardTemplate === $t['key']): echo 'checked'; endif; ?> <?php if($isLocked): echo 'disabled'; endif; ?> />

                            <!-- Thumbnail Preview -->
                            <div class="relative w-full overflow-hidden rounded-xl border border-gray-200 bg-white pointer-events-none select-none mb-4" style="aspect-ratio: 1 / 1.414;">
                                <div class="absolute inset-0" style="width: 200%; height: 200%; transform: scale(0.5); transform-origin: top left;">
                                    <iframe src="<?php echo e($t['preview']); ?>?html=1" class="w-full h-full border-0 bg-transparent" scrolling="no" tabindex="-1"></iframe>
                                </div>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-gray-900 flex items-center gap-2">
                                        <?php echo e($t['title']); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isLocked): ?>
                                            <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                            </svg>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="mt-1 text-xs font-semibold text-gray-600 line-clamp-2" title="<?php echo e($t['desc']); ?>"><?php echo e($t['desc']); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isLocked): ?>
                                        <div class="mt-2 text-[10px] font-bold text-red-600 uppercase tracking-wider">🔒 Premium License Required</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full <?php echo e($t['free'] ? 'bg-green-100 text-green-800' : 'bg-emerald-100 text-emerald-800'); ?> px-3 py-1 text-[10px] font-black group-hover:<?php echo e($t['free'] ? 'bg-green-200' : 'bg-emerald-200'); ?>">
                                    <?php echo e($t['free'] ? 'FREE' : 'PRO'); ?>

                                </span>
                            </div>

                            <div class="mt-auto pt-4 flex items-center justify-between">
                                <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">
                                    <?php echo e($isLocked ? 'Locked' : 'Click to select'); ?></div>
                                <button type="button" class="rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 border border-slate-200 transition-colors"
                                    @click.stop="open = true; src = <?php echo \Illuminate\Support\Js::from($t['preview'])->toHtml() ?>; title = <?php echo \Illuminate\Support\Js::from('Report Card · ' . $t['title'])->toHtml() ?>">
                                    Full Preview
                                </button>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-violet-50 to-indigo-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Report Card Display Options</div>
                        <div class="text-sm font-semibold text-gray-600">Toggle which sections appear on the report card</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_position" value="0" />
                        <input type="checkbox" name="rc_show_position" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowPosition): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Student Position</div>
                            <div class="text-xs text-gray-500">Show class position/ranking</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_attendance" value="0" />
                        <input type="checkbox" name="rc_show_attendance" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowAttendance): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Attendance Record</div>
                            <div class="text-xs text-gray-500">Show times opened/present/absent</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_grading_key" value="0" />
                        <input type="checkbox" name="rc_show_grading_key" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowGradingKey): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Grading Key</div>
                            <div class="text-xs text-gray-500">Show A-F grading legend</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_class_average" value="0" />
                        <input type="checkbox" name="rc_show_class_average" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowClassAverage): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Class Average / Highest / Lowest</div>
                            <div class="text-xs text-gray-500">Show class average, highest, and lowest scores</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_watermark" value="0" />
                        <input type="checkbox" name="rc_show_watermark" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowWatermark): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">School Logo Watermark</div>
                            <div class="text-xs text-gray-500">Faint logo watermark in background</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_next_term_date" value="0" />
                        <input type="checkbox" name="rc_show_next_term_date" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowNextTermDate): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Next Term Resumption Date</div>
                            <div class="text-xs text-gray-500">Show when next term begins</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_teacher_remarks" value="0" />
                        <input type="checkbox" name="rc_show_teacher_remarks" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowTeacherRemarks): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Teacher's Remarks</div>
                            <div class="text-xs text-gray-500">Show class teacher comment</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_principal_remarks" value="0" />
                        <input type="checkbox" name="rc_show_principal_remarks" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowPrincipalRemarks): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Principal's Remarks</div>
                            <div class="text-xs text-gray-500">Show principal/head teacher comment</div>
                        </div>
                    </label>

                    
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_psychomotor" value="0" />
                        <input type="checkbox" name="rc_show_psychomotor" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" <?php if($rcShowPsychomotor): echo 'checked'; endif; ?> />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Psychomotor Domain</div>
                            <div class="text-xs text-gray-500">Handwriting, sports, punctuality, neatness, etc.</div>
                        </div>
                    </label>
                </div>
            </div>

            
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-orange-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">School Fees for Next Term</div>
                        <div class="text-sm font-semibold text-gray-600">Show tuition fees and bank details on the report card</div>
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-amber-300 transition mb-4">
                    <input type="hidden" name="rc_show_school_fees" value="0" />
                    <input type="checkbox" name="rc_show_school_fees" value="1"
                        class="h-5 w-5 rounded-lg border-gray-300 text-amber-600 focus:ring-amber-500"
                        x-model="showSchoolFees"
                        <?php if($rcShowSchoolFees): echo 'checked'; endif; ?> />
                    <div>
                        <div class="text-sm font-bold text-gray-900">Enable School Fees on Report Card</div>
                        <div class="text-xs text-gray-500">When enabled, each class's tuition and account details will be printed</div>
                    </div>
                </label>

                <div x-show="showSchoolFees" x-transition class="space-y-4">
                    
                    <div class="rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
                        <div class="text-sm font-bold text-gray-900 mb-3">Bank Account Details</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Bank Name</label>
                                <input name="rc_school_fees_bank_name"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="<?php echo e($rcSchoolFeesBankName); ?>" placeholder="e.g. First Bank" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Account Number</label>
                                <input name="rc_school_fees_account_number"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="<?php echo e($rcSchoolFeesAccountNumber); ?>" placeholder="e.g. 0123456789" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Account Name</label>
                                <input name="rc_school_fees_account_name"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="<?php echo e($rcSchoolFeesAccountName); ?>" placeholder="e.g. School Name Ltd" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
                        <div class="text-sm font-bold text-gray-900 mb-1">Fees Per Class</div>
                        <div class="text-xs text-gray-500 mb-4">Set the tuition amount for each class (leave empty to skip that class)</div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-gray-800"><?php echo e($class->name); ?></div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-bold text-gray-500"><?php echo e(config('myacademy.currency_symbol', '₦')); ?></span>
                                        <input name="rc_school_fees_by_class[<?php echo e($class->id); ?>]"
                                            type="number" step="0.01" min="0"
                                            class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            value="<?php echo e($rcSchoolFeesByClass[$class->id] ?? ''); ?>"
                                            placeholder="0.00" />
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-rose-50 to-pink-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-lg shadow-rose-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Signatures on Report Card</div>
                        <div class="text-sm font-semibold text-gray-600">Upload actual signature images to print on report cards</div>
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-rose-300 transition mb-4">
                    <input type="hidden" name="rc_show_signatures" value="0" />
                    <input type="checkbox" name="rc_show_signatures" value="1"
                        class="h-5 w-5 rounded-lg border-gray-300 text-rose-600 focus:ring-rose-500"
                        x-model="showSignatures"
                        <?php if($rcShowSignatures): echo 'checked'; endif; ?> />
                    <div>
                        <div class="text-sm font-bold text-gray-900">Enable Signature Images</div>
                        <div class="text-xs text-gray-500">When enabled, uploaded signatures will appear above the signature lines</div>
                    </div>
                </label>

                <div x-show="showSignatures" x-transition class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-5 shadow-sm">
                            <div class="text-sm font-bold text-gray-900 mb-3">Principal / Head Teacher Signature</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rcPrincipalSignatureImage): ?>
                                <div class="mb-3 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <img src="<?php echo e(asset('uploads/' . str_replace('\\', '/', $rcPrincipalSignatureImage))); ?>"
                                        alt="Principal Signature" class="h-12 object-contain" />
                                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="rc_principal_signature_remove" value="1" class="rounded border-gray-300 text-red-500" />
                                        Remove
                                    </label>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <input name="rc_principal_signature_image" type="file" accept="image/*"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500" />
                            <div class="mt-1 text-xs text-gray-500">Upload a transparent PNG for best results (max 2MB)</div>
                        </div>

                        
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-5 shadow-sm">
                            <div class="text-sm font-bold text-gray-900 mb-3">Class Teacher Signature</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rcTeacherSignatureImage): ?>
                                <div class="mb-3 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <img src="<?php echo e(asset('uploads/' . str_replace('\\', '/', $rcTeacherSignatureImage))); ?>"
                                        alt="Teacher Signature" class="h-12 object-contain" />
                                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="rc_teacher_signature_remove" value="1" class="rounded border-gray-300 text-red-500" />
                                        Remove
                                    </label>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <input name="rc_teacher_signature_image" type="file" accept="image/*"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500" />
                            <div class="mt-1 text-xs text-gray-500">Upload a transparent PNG for best results (max 2MB)</div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-slate-800 transition-all">
                Save Report Card Settings
            </button>
        </form>

        <!-- Preview Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
            @click.self="open = false">
            <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="text-sm font-black text-slate-900" x-text="title"></div>
                    <button type="button"
                        class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200"
                        @click="open = false">
                        Close
                    </button>
                </div>
                <div class="h-[80vh] bg-slate-50">
                    <iframe :src="src" class="h-full w-full" title="Template Preview"></iframe>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/settings/templates.blade.php ENDPATH**/ ?>