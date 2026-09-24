<?php

use App\Models\HomepageContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = new HomepageContent;
        $homepage = HomepageContent::query()->where('site_key', HomepageContent::DEFAULT_SITE_KEY)->first();

        if ($homepage) {
            // Saved satellite-service copy overrides model defaults on existing sites.
            $legacyMarkers = ['satellite internet', 'starlink', 'dish alignment', 'kit includes the dish', 'speeds vary by location, weather'];

            if (Str::contains(strtolower(json_encode([$homepage->faq_title, $homepage->faq_intro, $homepage->faq_items])), $legacyMarkers)) {
                $homepage->faq_title = $defaults->faqTitle();
                $homepage->faq_intro = $defaults->faqIntro();
                $homepage->faq_items = $defaults->faqItems();
            }

            if (Str::contains(strtolower(json_encode([$homepage->why_choose_intro, $homepage->why_choose_items])), [
                ...$legacyMarkers, 'monthly service', 'clean alignment, mounting', 'dependable high-speed internet for homes',
            ])) {
                $homepage->why_choose_title = $defaults->whyChooseTitle();
                $homepage->why_choose_intro = $defaults->whyChooseIntro();
                $homepage->why_choose_items = $defaults->whyChooseItems();
            }

            if (Str::contains(strtolower((string) $homepage->testimonials_intro), $legacyMarkers)) {
                $homepage->testimonials_title = $defaults->testimonialsTitle();
                $homepage->testimonials_intro = $defaults->testimonialsIntro();
            }

            if ($homepage->isDirty()) {
                $homepage->save();
            }
        }

        // These quotes were bundled demo content, including the rebranded version.
        // Retain the records in admin for review; do not present them as customer proof.
        DB::table('testimonials')->where(function ($query): void {
            $query->where('quote', 'like', 'The installation team arrived on time, explained the ideal mounting position,%')
                ->orWhere('quote', 'like', 'Our children now attend online classes without interruptions, and video meetings are finally stable.%')
                ->orWhere('quote', 'like', 'Uploads that used to take forever now finish quickly, which matters a lot for my content work.%');
        })->where('is_active', true)->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // A rollback must not republish misleading copy or unverified sample reviews.
    }
};
