<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Support\TenantSettings;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function store(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sections', 'name')->where(fn ($q) => $q
                    ->where('class_id', $class->id)
                    ->where('tenant_id', TenantSettings::tenantId())),
            ],
        ]);

        Section::query()->create([
            'class_id' => $class->id,
            'name' => strtoupper(trim($data['name'])),
        ]);

        return back()->with('status', 'Section added.');
    }

    public function update(Request $request, SchoolClass $class, Section $section)
    {
        abort_unless((int) $section->class_id === (int) $class->id, 404);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sections', 'name')
                    ->where(fn ($q) => $q
                        ->where('class_id', $class->id)
                        ->where('tenant_id', TenantSettings::tenantId()))
                    ->ignore($section->id),
            ],
        ]);

        $section->update([
            'name' => strtoupper(trim($data['name'])),
        ]);

        return back()->with('status', 'Section updated.');
    }

    public function destroy(SchoolClass $class, Section $section)
    {
        abort_unless((int) $section->class_id === (int) $class->id, 404);

        try {
            $section->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['section' => 'Unable to delete this section. Remove dependent records first.']);
        }

        return back()->with('status', 'Section deleted.');
    }
}

