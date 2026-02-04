<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Artist;
use App\Models\ArtistAvailability;
use App\Models\ArtistTimeOff;
use App\Models\PortfolioImage;
use App\Models\Service;
use App\Models\Studio;
use App\Models\User;
use App\Models\Waiver;
use Database\Factories\ArtistAvailabilityFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed roles and permissions
        $this->call(RoleSeeder::class);

        // Create Demo Studio
        $demoStudio = Studio::create([
            'name' => 'Ink & Soul Tattoo',
            'slug' => 'demo',
            'primary_color' => '#f59e0b',
            'email' => 'hello@inkandsoul.com',
            'phone' => '(555) 123-4567',
            'address' => '123 Main Street, Portland, OR 97201',
            'timezone' => 'America/Los_Angeles',
            'settings' => [
                'tagline' => 'Where Art Meets Skin',
                'about_text' => '<p>Welcome to <strong>Ink & Soul Tattoo</strong>, Portland\'s premier custom tattoo studio. Founded in 2015, we\'ve built our reputation on creating unique, meaningful artwork that tells your story.</p><p>Our team of experienced artists specializes in a wide range of styles, from bold traditional American to delicate fine line work. Every piece we create is a collaboration between artist and client, ensuring your tattoo is truly one-of-a-kind.</p><p>We maintain the highest standards of cleanliness and safety, using single-use needles and hospital-grade sterilization equipment. Your comfort and safety are our top priorities.</p>',
                'meta_description' => 'Ink & Soul Tattoo is Portland\'s premier custom tattoo studio. Book your appointment with our talented artists today.',
                'social_links' => [
                    'instagram' => 'https://instagram.com/inkandsoul',
                    'facebook' => 'https://facebook.com/inkandsoul',
                    'tiktok' => 'https://tiktok.com/@inkandsoul',
                    'yelp' => 'https://yelp.com/biz/ink-and-soul-tattoo-portland',
                ],
                'business_hours' => [
                    ['day' => 'Monday', 'open' => '11:00', 'close' => '19:00', 'is_closed' => false],
                    ['day' => 'Tuesday', 'open' => '11:00', 'close' => '19:00', 'is_closed' => false],
                    ['day' => 'Wednesday', 'open' => '11:00', 'close' => '19:00', 'is_closed' => false],
                    ['day' => 'Thursday', 'open' => '11:00', 'close' => '20:00', 'is_closed' => false],
                    ['day' => 'Friday', 'open' => '11:00', 'close' => '20:00', 'is_closed' => false],
                    ['day' => 'Saturday', 'open' => '10:00', 'close' => '18:00', 'is_closed' => false],
                    ['day' => 'Sunday', 'open' => '10:00', 'close' => '18:00', 'is_closed' => true],
                ],
                'booking_enabled' => true,
                'booking_minimum_notice_hours' => 48,
                'booking_deposit_type' => 'percentage',
                'booking_deposit_amount' => 20,
                'booking_instructions' => 'Please include reference images and a description of your tattoo idea. A 20% deposit is required to secure your appointment. Deposits are non-refundable but can be applied to a rescheduled appointment with at least 48 hours notice.',
            ],
        ]);

        // Create Demo Studio owner (also super_admin for demo purposes)
        $demoOwner = User::factory()->create([
            'name' => 'Demo Owner',
            'email' => 'demo@example.com',
        ]);
        $demoOwner->assignRole('super_admin'); // Demo user gets super_admin for testing
        $demoOwner->assignRole('owner');
        $demoStudio->members()->attach($demoOwner, ['role' => 'owner']);

        // Create Demo Studio artist users
        $demoArtist1User = User::factory()->create([
            'name' => 'Alex Rivera',
            'email' => 'alex@inkandsoul.com',
        ]);
        $demoArtist1User->assignRole('artist');
        $demoStudio->members()->attach($demoArtist1User, ['role' => 'artist']);

        $demoArtist2User = User::factory()->create([
            'name' => 'Jordan Chen',
            'email' => 'jordan@inkandsoul.com',
        ]);
        $demoArtist2User->assignRole('artist');
        $demoStudio->members()->attach($demoArtist2User, ['role' => 'artist']);

        // Create an editor for demo studio
        $demoEditor = User::factory()->create([
            'name' => 'Sam Manager',
            'email' => 'sam@inkandsoul.com',
        ]);
        $demoEditor->assignRole('editor');
        $demoStudio->members()->attach($demoEditor, ['role' => 'editor']);

        // Create an apprentice for demo studio
        $demoApprentice = User::factory()->create([
            'name' => 'Taylor Newbie',
            'email' => 'taylor@inkandsoul.com',
        ]);
        $demoApprentice->assignRole('apprentice');
        $demoStudio->members()->attach($demoApprentice, ['role' => 'apprentice']);

        // Create Artist profiles for Demo Studio
        $alexArtist = Artist::create([
            'studio_id' => $demoStudio->id,
            'user_id' => $demoArtist1User->id,
            'display_name' => 'Alex Rivera',
            'slug' => 'alex-rivera',
            'bio' => "Alex has been tattooing for over 10 years, specializing in bold traditional American designs and Japanese-inspired work. Their attention to detail and mastery of color theory makes every piece a standout.\n\nAlex draws inspiration from vintage flash art, Japanese ukiyo-e prints, and the natural world. When not tattooing, they can be found painting or exploring the Pacific Northwest.",
            'specialties' => ['Traditional', 'Japanese', 'Neo-Traditional'],
            'instagram_handle' => '@alexriveratattoo',
            'hourly_rate' => 175.00,
            'is_active' => true,
            'is_accepting_bookings' => true,
            'sort_order' => 1,
        ]);

        // Create portfolio images for Alex
        PortfolioImage::create([
            'artist_id' => $alexArtist->id,
            'image_path' => 'portfolio/alex-traditional-eagle.jpg',
            'title' => 'Traditional Eagle',
            'description' => 'Classic American traditional eagle chest piece',
            'style' => 'Traditional',
            'sort_order' => 1,
            'is_featured' => true,
        ]);
        PortfolioImage::create([
            'artist_id' => $alexArtist->id,
            'image_path' => 'portfolio/alex-japanese-dragon.jpg',
            'title' => 'Japanese Dragon Sleeve',
            'description' => 'Full sleeve featuring a traditional Japanese dragon with cherry blossoms',
            'style' => 'Japanese',
            'sort_order' => 2,
            'is_featured' => true,
        ]);
        PortfolioImage::create([
            'artist_id' => $alexArtist->id,
            'image_path' => 'portfolio/alex-neo-trad-panther.jpg',
            'title' => 'Neo-Traditional Panther',
            'description' => 'Bold panther head with modern color palette',
            'style' => 'Neo-Traditional',
            'sort_order' => 3,
            'is_featured' => false,
        ]);

        $jordanArtist = Artist::create([
            'studio_id' => $demoStudio->id,
            'user_id' => $demoArtist2User->id,
            'display_name' => 'Jordan Chen',
            'slug' => 'jordan-chen',
            'bio' => "Jordan specializes in fine line work and geometric designs, bringing a modern minimalist aesthetic to the tattoo world. Their precise linework and thoughtful compositions create elegant, timeless pieces.\n\nWith a background in graphic design, Jordan approaches each tattoo as a carefully considered work of art. They are particularly passionate about botanical illustrations and sacred geometry.",
            'specialties' => ['Fine Line', 'Geometric', 'Minimalist', 'Dotwork'],
            'instagram_handle' => '@jordanchentattoo',
            'hourly_rate' => 150.00,
            'is_active' => true,
            'is_accepting_bookings' => true,
            'sort_order' => 2,
        ]);

        // Create portfolio images for Jordan
        PortfolioImage::create([
            'artist_id' => $jordanArtist->id,
            'image_path' => 'portfolio/jordan-fine-line-botanical.jpg',
            'title' => 'Botanical Fine Line',
            'description' => 'Delicate wildflower arrangement on forearm',
            'style' => 'Fine Line',
            'sort_order' => 1,
            'is_featured' => true,
        ]);
        PortfolioImage::create([
            'artist_id' => $jordanArtist->id,
            'image_path' => 'portfolio/jordan-geometric-mandala.jpg',
            'title' => 'Sacred Geometry Mandala',
            'description' => 'Intricate geometric mandala with dotwork shading',
            'style' => 'Geometric',
            'sort_order' => 2,
            'is_featured' => true,
        ]);
        PortfolioImage::create([
            'artist_id' => $jordanArtist->id,
            'image_path' => 'portfolio/jordan-minimalist-wave.jpg',
            'title' => 'Minimalist Wave',
            'description' => 'Simple wave design inspired by Hokusai',
            'style' => 'Minimalist',
            'sort_order' => 3,
            'is_featured' => false,
        ]);

        // Create a guest artist (no user account)
        $guestArtist = Artist::create([
            'studio_id' => $demoStudio->id,
            'user_id' => null,
            'display_name' => 'Maya Blackwood',
            'slug' => 'maya-blackwood',
            'bio' => "Maya is a guest artist visiting from Austin, TX. She specializes in blackwork and illustrative tattoos with a dark, fantastical aesthetic.\n\nBooking for a limited time only!",
            'specialties' => ['Blackwork', 'Illustrative', 'Dark Art'],
            'instagram_handle' => '@mayablackwoodtattoo',
            'hourly_rate' => 200.00,
            'is_active' => true,
            'is_accepting_bookings' => true,
            'sort_order' => 3,
        ]);

        PortfolioImage::create([
            'artist_id' => $guestArtist->id,
            'image_path' => 'portfolio/maya-blackwork-moth.jpg',
            'title' => 'Death Moth',
            'description' => 'Detailed blackwork moth with skull pattern',
            'style' => 'Blackwork',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        // Create Services for Demo Studio
        $consultationService = Service::create([
            'studio_id' => $demoStudio->id,
            'name' => 'Consultation',
            'slug' => 'consultation',
            'description' => 'Free consultation to discuss your tattoo idea, placement, and pricing. Come prepared with reference images!',
            'duration_minutes' => 30,
            'price_type' => 'consultation',
            'price' => null,
            'deposit_required' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $smallTattooService = Service::create([
            'studio_id' => $demoStudio->id,
            'name' => 'Small Tattoo',
            'slug' => 'small-tattoo',
            'description' => 'Perfect for pieces under 2 inches. Includes simple designs, words, or small symbols.',
            'duration_minutes' => 60,
            'price_type' => 'fixed',
            'price' => 150.00,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $mediumPieceService = Service::create([
            'studio_id' => $demoStudio->id,
            'name' => 'Medium Piece',
            'slug' => 'medium-piece',
            'description' => 'For tattoos between 2-6 inches. Charged by the hour at the artist\'s rate.',
            'duration_minutes' => 180,
            'price_type' => 'hourly',
            'price' => null,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $largePieceService = Service::create([
            'studio_id' => $demoStudio->id,
            'name' => 'Large Piece',
            'slug' => 'large-piece',
            'description' => 'For pieces larger than 6 inches. May require multiple sessions. Charged by the hour.',
            'duration_minutes' => 360,
            'price_type' => 'hourly',
            'price' => null,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $touchUpService = Service::create([
            'studio_id' => $demoStudio->id,
            'name' => 'Touch-up',
            'slug' => 'touch-up',
            'description' => 'Free touch-ups within 3 months of original tattoo from our studio. After 3 months, a flat fee applies.',
            'duration_minutes' => 45,
            'price_type' => 'fixed',
            'price' => 75.00,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // Create Artist Availability for Demo Studio artists
        // Alex: Mon-Fri 10am-6pm, Sat 11am-5pm
        ArtistAvailabilityFactory::createStandardWeekForArtist($alexArtist);

        // Jordan: Same schedule
        ArtistAvailabilityFactory::createStandardWeekForArtist($jordanArtist);

        // Maya (guest): Only available Thu-Sat 12pm-8pm
        ArtistAvailability::create([
            'artist_id' => $guestArtist->id,
            'day_of_week' => ArtistAvailability::THURSDAY,
            'start_time' => '12:00',
            'end_time' => '20:00',
            'is_available' => true,
        ]);
        ArtistAvailability::create([
            'artist_id' => $guestArtist->id,
            'day_of_week' => ArtistAvailability::FRIDAY,
            'start_time' => '12:00',
            'end_time' => '20:00',
            'is_available' => true,
        ]);
        ArtistAvailability::create([
            'artist_id' => $guestArtist->id,
            'day_of_week' => ArtistAvailability::SATURDAY,
            'start_time' => '12:00',
            'end_time' => '20:00',
            'is_available' => true,
        ]);

        // Add some time off for Alex (vacation next month)
        ArtistTimeOff::create([
            'artist_id' => $alexArtist->id,
            'start_date' => now()->addMonth()->startOfWeek(),
            'end_date' => now()->addMonth()->startOfWeek()->addDays(4),
            'reason' => 'Vacation',
            'is_all_day' => true,
        ]);

        // Create sample appointments for Demo Studio
        // Upcoming confirmed appointment for Alex
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $alexArtist->id,
            'service_id' => $mediumPieceService->id,
            'client_name' => 'Sarah Johnson',
            'client_email' => 'sarah.j@example.com',
            'client_phone' => '(555) 234-5678',
            'scheduled_at' => now()->addDays(3)->setHour(10)->setMinute(0),
            'duration_minutes' => 180,
            'status' => Appointment::STATUS_CONFIRMED,
            'tattoo_description' => 'Traditional style rose with leaves, about 4 inches',
            'tattoo_placement' => 'Upper Arm',
            'estimated_price' => 525.00,
            'deposit_amount' => 105.00,
            'deposit_paid_at' => now()->subDays(5),
            'payment_method' => 'stripe',
        ]);

        // Pending appointment for Jordan
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $jordanArtist->id,
            'service_id' => $smallTattooService->id,
            'client_name' => 'Mike Chen',
            'client_email' => 'mike.chen@example.com',
            'client_phone' => '(555) 345-6789',
            'scheduled_at' => now()->addDays(7)->setHour(14)->setMinute(0),
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_PENDING,
            'tattoo_description' => 'Small geometric mountain outline',
            'tattoo_placement' => 'Wrist',
            'estimated_price' => 150.00,
            'deposit_amount' => 30.00,
            'notes' => 'First tattoo - may need extra time for questions',
        ]);

        // Tomorrow's appointment
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $alexArtist->id,
            'service_id' => $consultationService->id,
            'client_name' => 'Emily Watson',
            'client_email' => 'emily.w@example.com',
            'client_phone' => '(555) 456-7890',
            'scheduled_at' => now()->addDay()->setHour(11)->setMinute(0),
            'duration_minutes' => 30,
            'status' => Appointment::STATUS_CONFIRMED,
            'tattoo_description' => 'Interested in a Japanese-style half sleeve',
            'notes' => 'Bringing reference images from Pinterest',
        ]);

        // Completed appointment
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $jordanArtist->id,
            'service_id' => $smallTattooService->id,
            'client_name' => 'David Park',
            'client_email' => 'david.park@example.com',
            'client_phone' => '(555) 567-8901',
            'scheduled_at' => now()->subDays(3)->setHour(15)->setMinute(0),
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_COMPLETED,
            'tattoo_description' => 'Fine line botanical - lavender sprig',
            'tattoo_placement' => 'Forearm',
            'estimated_price' => 150.00,
            'deposit_amount' => 30.00,
            'deposit_paid_at' => now()->subWeeks(2),
            'payment_method' => 'cash',
        ]);

        // Cancelled appointment
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $alexArtist->id,
            'service_id' => $largePieceService->id,
            'client_name' => 'Jessica Miller',
            'client_email' => 'jess.miller@example.com',
            'client_phone' => '(555) 678-9012',
            'scheduled_at' => now()->subDays(1)->setHour(10)->setMinute(0),
            'duration_minutes' => 360,
            'status' => Appointment::STATUS_CANCELLED,
            'tattoo_description' => 'Full back piece - phoenix rising',
            'tattoo_placement' => 'Back',
            'estimated_price' => 1200.00,
            'deposit_amount' => 240.00,
            'deposit_paid_at' => now()->subWeeks(3),
            'cancelled_at' => now()->subDays(3),
            'cancellation_reason' => 'Client requested - personal emergency',
        ]);

        // No-show appointment
        Appointment::create([
            'studio_id' => $demoStudio->id,
            'artist_id' => $guestArtist->id,
            'service_id' => $mediumPieceService->id,
            'client_name' => 'Tom Wilson',
            'client_email' => 'tom.w@example.com',
            'client_phone' => '(555) 789-0123',
            'scheduled_at' => now()->subWeek()->setHour(13)->setMinute(0),
            'duration_minutes' => 180,
            'status' => Appointment::STATUS_NO_SHOW,
            'tattoo_description' => 'Blackwork moth design',
            'tattoo_placement' => 'Chest',
            'estimated_price' => 400.00,
            'deposit_amount' => 80.00,
            'deposit_paid_at' => now()->subWeeks(2),
            'payment_method' => 'venmo',
            'artist_notes' => 'No-show, no response to calls. Deposit forfeited.',
        ]);

        // Create waivers for Demo Studio
        Waiver::factory(10)->create([
            'studio_id' => $demoStudio->id,
            'user_id' => $demoOwner->id,
        ]);

        // Create Dark Arts Studio (second tenant)
        $darkArtsStudio = Studio::create([
            'name' => 'Dark Arts Tattoo',
            'slug' => 'darkarts',
            'primary_color' => '#7c3aed',
            'email' => 'info@darkartstattoo.com',
            'phone' => '(555) 666-7777',
            'address' => '666 Shadow Lane, Seattle, WA 98101',
            'timezone' => 'America/Los_Angeles',
            'settings' => [
                'tagline' => 'Embrace the Darkness',
                'about_text' => '<p><strong>Dark Arts Tattoo</strong> is Seattle\'s home for blackwork, occult, and dark aesthetic tattoos. Our studio celebrates the beauty in darkness, creating powerful imagery that resonates with those who walk their own path.</p><p>Owner and lead artist Raven Nightshade has been tattooing for over 15 years, developing a distinctive style that blends traditional occult symbolism with modern techniques.</p><p>We operate by appointment only to ensure each client receives our full attention and dedication.</p>',
                'meta_description' => 'Dark Arts Tattoo - Seattle\'s premier studio for blackwork, occult, and dark aesthetic tattoos. Book with Raven Nightshade today.',
                'social_links' => [
                    'instagram' => 'https://instagram.com/darkartstattoo',
                    'facebook' => '',
                    'tiktok' => 'https://tiktok.com/@darkartstattoo',
                    'yelp' => 'https://yelp.com/biz/dark-arts-tattoo-seattle',
                ],
                'business_hours' => [
                    ['day' => 'Monday', 'open' => '12:00', 'close' => '20:00', 'is_closed' => true],
                    ['day' => 'Tuesday', 'open' => '12:00', 'close' => '20:00', 'is_closed' => false],
                    ['day' => 'Wednesday', 'open' => '12:00', 'close' => '20:00', 'is_closed' => false],
                    ['day' => 'Thursday', 'open' => '12:00', 'close' => '20:00', 'is_closed' => false],
                    ['day' => 'Friday', 'open' => '12:00', 'close' => '22:00', 'is_closed' => false],
                    ['day' => 'Saturday', 'open' => '12:00', 'close' => '22:00', 'is_closed' => false],
                    ['day' => 'Sunday', 'open' => '12:00', 'close' => '18:00', 'is_closed' => false],
                ],
                'booking_enabled' => true,
                'booking_minimum_notice_hours' => 72,
                'booking_deposit_type' => 'fixed',
                'booking_deposit_amount' => 100,
                'booking_instructions' => 'Dark Arts Tattoo operates by appointment only. Please provide detailed reference images and your vision for the piece. A $100 non-refundable deposit is required. We specialize in blackwork, occult, and dark aesthetic pieces.',
            ],
        ]);

        // Create Dark Arts Studio owner
        $darkArtsOwner = User::factory()->create([
            'name' => 'Dark Arts Owner',
            'email' => 'owner@darkartstattoo.com',
        ]);
        $darkArtsOwner->assignRole('owner');
        $darkArtsStudio->members()->attach($darkArtsOwner, ['role' => 'owner']);

        // Create an artist for Dark Arts Studio
        $darkArtsArtist = Artist::create([
            'studio_id' => $darkArtsStudio->id,
            'user_id' => $darkArtsOwner->id,
            'display_name' => 'Raven Nightshade',
            'slug' => 'raven-nightshade',
            'bio' => "Raven is the owner and lead artist at Dark Arts Tattoo. With 15 years of experience, they specialize in dark, occult-themed tattoos and bold blackwork pieces.",
            'specialties' => ['Blackwork', 'Dark Art', 'Occult', 'Realism'],
            'instagram_handle' => '@ravennightshade',
            'hourly_rate' => 225.00,
            'is_active' => true,
            'is_accepting_bookings' => true,
            'sort_order' => 1,
        ]);

        PortfolioImage::create([
            'artist_id' => $darkArtsArtist->id,
            'image_path' => 'portfolio/raven-occult-moon.jpg',
            'title' => 'Occult Moon Phase',
            'description' => 'Moon phases with occult symbols and botanicals',
            'style' => 'Blackwork',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        // Create Services for Dark Arts Studio
        Service::create([
            'studio_id' => $darkArtsStudio->id,
            'name' => 'Consultation',
            'slug' => 'consultation',
            'description' => 'Discuss your vision for your dark art piece.',
            'duration_minutes' => 45,
            'price_type' => 'consultation',
            'price' => null,
            'deposit_required' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::create([
            'studio_id' => $darkArtsStudio->id,
            'name' => 'Blackwork Session',
            'slug' => 'blackwork-session',
            'description' => 'Full blackwork session. Minimum 2 hours.',
            'duration_minutes' => 240,
            'price_type' => 'hourly',
            'price' => 225.00,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Service::create([
            'studio_id' => $darkArtsStudio->id,
            'name' => 'Full Day Session',
            'slug' => 'full-day-session',
            'description' => 'Book Raven for a full day (6-8 hours) for larger pieces.',
            'duration_minutes' => 420,
            'price_type' => 'fixed',
            'price' => 1500.00,
            'deposit_required' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Availability for Dark Arts artist (Raven)
        // Tue-Sat 12pm-8pm
        for ($day = 2; $day <= 6; $day++) {
            ArtistAvailability::create([
                'artist_id' => $darkArtsArtist->id,
                'day_of_week' => $day,
                'start_time' => '12:00',
                'end_time' => '20:00',
                'is_available' => true,
            ]);
        }

        // Create waivers for Dark Arts Studio
        Waiver::factory(5)->create([
            'studio_id' => $darkArtsStudio->id,
            'user_id' => $darkArtsOwner->id,
        ]);

        // Create some unsigned waivers for demo studio
        Waiver::factory(3)->create([
            'studio_id' => $demoStudio->id,
            'user_id' => null,
            'signed_at' => null,
            'signature' => null,
            'accepted_terms' => false,
            'accepted_aftercare' => false,
        ]);
    }
}
