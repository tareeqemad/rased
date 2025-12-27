<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Check if user is SuperAdmin
     */
    private function checkSuperAdmin()
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
    }

    public function index(): View
    {
        $this->checkSuperAdmin();
        
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'settings' => 'required|array',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,ico,svg|max:512',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'rased_logo.' . $logo->getClientOriginalExtension();
            
            // Copy to public directory
            $publicPath = public_path('assets/admin/images/brand-logos');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            $logo->move($publicPath, $logoName);
            
            Setting::set('site_logo', 'assets/admin/images/brand-logos/' . $logoName, 'image', 'logo', 'لوجو الموقع', 'لوجو الموقع الرئيسي');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconName = 'favicon.' . $favicon->getClientOriginalExtension();
            
            // Copy to public directory
            $publicPath = public_path('assets/admin/images/brand-logos');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            $favicon->move($publicPath, $faviconName);
            
            Setting::set('site_favicon', 'assets/admin/images/brand-logos/' . $faviconName, 'image', 'logo', 'أيقونة الموقع', 'أيقونة الموقع (Favicon)');
        }

        // Update other settings
        foreach ($request->input('settings', []) as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => 'text',
                    'group' => 'general',
                ]);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم تحديث الإعدادات بنجاح.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:text,textarea,number,image,email,url',
            'group' => 'required|string',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Setting::create($request->only(['key', 'value', 'type', 'group', 'label', 'description']));

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم إضافة الإعداد بنجاح.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        // Don't allow deletion of critical settings
        $criticalSettings = ['site_logo', 'site_favicon', 'site_name'];
        if (in_array($setting->key, $criticalSettings)) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'لا يمكن حذف هذا الإعداد لأنه إعداد أساسي.');
        }

        $setting->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم حذف الإعداد بنجاح.');
    }
}
