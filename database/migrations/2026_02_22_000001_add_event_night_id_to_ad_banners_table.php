<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->foreignId('event_night_id')->nullable()->after('venue_id')->constrained()->cascadeOnDelete();
            $table->index(['event_night_id', 'is_active']);
        });

        $now = now();

        $eventsByBannerId = DB::table('event_nights')
            ->select(['id', 'venue_id', 'ad_banner_id'])
            ->whereNotNull('ad_banner_id')
            ->orderBy('id')
            ->get()
            ->groupBy('ad_banner_id');

        foreach ($eventsByBannerId as $bannerId => $events) {
            $banner = DB::table('ad_banners')->where('id', (int) $bannerId)->first();
            if ($banner === null) {
                continue;
            }

            $orderedEvents = $events->values();
            $primaryEvent = $orderedEvents->first();
            if ($primaryEvent === null) {
                continue;
            }

            DB::table('ad_banners')
                ->where('id', (int) $bannerId)
                ->update([
                    'event_night_id' => $primaryEvent->id,
                    'venue_id' => $primaryEvent->venue_id,
                    'updated_at' => $now,
                ]);

            foreach ($orderedEvents->slice(1) as $event) {
                $clonedBannerId = DB::table('ad_banners')->insertGetId([
                    'event_night_id' => $event->id,
                    'venue_id' => $event->venue_id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle ?? null,
                    'image_url' => $banner->image_url,
                    'logo_url' => $banner->logo_url ?? null,
                    'is_active' => (bool) $banner->is_active,
                    'created_at' => $banner->created_at ?? $now,
                    'updated_at' => $banner->updated_at ?? $now,
                ]);

                DB::table('event_nights')
                    ->where('id', $event->id)
                    ->update(['ad_banner_id' => $clonedBannerId]);
            }
        }

        $legacyBanners = DB::table('ad_banners')
            ->select(['id', 'venue_id'])
            ->whereNull('event_night_id')
            ->orderBy('id')
            ->get();

        foreach ($legacyBanners as $legacyBanner) {
            $fallbackEventId = DB::table('event_nights')
                ->where('venue_id', $legacyBanner->venue_id)
                ->orderByDesc('starts_at')
                ->orderByDesc('id')
                ->value('id');

            if ($fallbackEventId === null) {
                continue;
            }

            DB::table('ad_banners')
                ->where('id', $legacyBanner->id)
                ->update([
                    'event_night_id' => $fallbackEventId,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->dropIndex('ad_banners_event_night_id_is_active_index');
            $table->dropForeign(['event_night_id']);
            $table->dropColumn('event_night_id');
        });
    }
};
