<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">

        {{-- ============================== ORIENTATION ============================== --}}
        <x-filament::section collapsible>
            <x-slot name="heading">Start here: Courses vs Programs</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <p>The platform sells learning in two different ways. Picking the right one is the most important decision when adding something new:</p>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <p class="font-semibold">Course (Catalogue &rarr; Products)</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-gray-600 dark:text-gray-300">
                            <li>Visitors browse it on the public <strong>Courses</strong> page and pay for themselves at checkout.</li>
                            <li>Right for adults and self-paying students.</li>
                            <li>Price, level, cohort dates and curriculum all live on the product.</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <p class="font-semibold">Program (Programs section)</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-gray-600 dark:text-gray-300">
                            <li>Parents/guardians <strong>register a child</strong> into an age track, then pay to secure the seat.</li>
                            <li>Right for kids/teens bootcamps (e.g. Summer AI Program).</li>
                            <li>Built from three pieces: <strong>Program &rarr; Edition &rarr; Age tracks</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- ============================== COURSES ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Adding a course</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <ol class="list-decimal space-y-2 pl-5">
                    <li><strong>Prerequisites</strong> (each under Catalogue): a <strong>Track</strong> (subject area), a <strong>Course Level</strong> (e.g. Beginner), and optionally a <strong>Cohort</strong> (a dated run, e.g. &ldquo;Summer 2026&rdquo; &mdash; gives the course its start date and enrollment deadline).</li>
                    <li><strong>Catalogue &rarr; Products &rarr; New</strong>: title (the slug fills itself), track, level, cohort, delivery mode, description, curriculum weeks, media, tags.</li>
                    <li><strong>Add a price</strong> &mdash; see the pricing section below. Without an <em>active default price</em> the course cannot be checked out.</li>
                    <li><strong>Publish</strong>: set Status to <em>Published</em> and make sure <em>Published at</em> is now or in the past. A future date <strong>schedules</strong> the course &mdash; it stays invisible until that moment.</li>
                </ol>

                <div class="rounded-lg bg-amber-50 p-4 text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                    <p class="font-semibold">Course not showing on the website?</p>
                    <ul class="mt-1 list-disc space-y-1 pl-5">
                        <li>Status must be <strong>Published</strong> (not Draft/Hidden).</li>
                        <li><strong>Published at</strong> must not be in the future &mdash; all dates here are Nigerian time (WAT).</li>
                        <li>It must have an <strong>active default price</strong>.</li>
                        <li>Listings are search-powered; a fresh publish can take a minute to be indexed. Then refresh the page.</li>
                    </ul>
                </div>

                <p><strong>Product statuses:</strong> <em>Draft</em> = work in progress, invisible. <em>Published</em> = live on the site. <em>Hidden</em> = buyable via a direct link or a program track but never listed publicly (this is how program-track pricing works &mdash; don&rsquo;t &ldquo;fix&rdquo; those to Published). <em>Sold out / Archived</em> = closed.</p>
            </div>
        </x-filament::section>

        {{-- ============================== PRICING ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Prices, early-bird offers and installments</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <p><strong>Catalogue &rarr; Product Prices &rarr; New</strong>, attached to the product:</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li><strong>Amount is in full naira</strong>: type <code>150000</code> for &#8358;150,000. Never multiply by 100 &mdash; checkout converts to kobo for Paystack automatically.</li>
                    <li>Exactly one price should be <strong>Default + Active</strong> &mdash; that is what checkout charges.</li>
                    <li><strong>Compare-at amount</strong> shows a struck-through &ldquo;was&rdquo; price for promotions.</li>
                    <li><strong>Early bird pattern</strong>: create the early price as Default with an <em>Ends at</em> deadline, and a standard price that <em>Starts at</em> the same moment.</li>
                    <li><strong>Installments</strong>: create a plan under Catalogue &rarr; Product Payment Plans (deposit + scheduled balance). The learner sees the option at checkout; reminder emails go out automatically.</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- ============================== PROGRAMS ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Running a program (editions, age tracks, waitlists)</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <p>A program is a reusable brand (&ldquo;Summer AI Program&rdquo;). Each time it runs, you create an <strong>Edition</strong> (&ldquo;Summer 2026&rdquo;). Children are placed into the edition&rsquo;s <strong>age tracks</strong> automatically based on date of birth.</p>

                <ol class="list-decimal space-y-2 pl-5">
                    <li><strong>Programs &rarr; New</strong> (once per brand): name, tagline, description, Active on.</li>
                    <li><strong>Program Editions &rarr; New</strong>: year, title, dates, schedule text, venue, and:
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            <li><strong>Age reference date</strong> &mdash; the date ages are counted on. Use the program&rsquo;s first day. A wrong year here (e.g. 2001 instead of 2026) produces nonsense like &ldquo;a &minus;6-year-old&rdquo; and blocks every registration.</li>
                            <li><strong>Status drives everything</strong>: <em>Draft</em> (invisible) &rarr; <em>Announced</em> (visible, &ldquo;registration not open yet&rdquo;) &rarr; <em>Registration open</em> &rarr; <em>Sold out</em> / <em>Running</em> / <em>Completed</em>. There is no auto-close date &mdash; <strong>you close registration by changing the status</strong>.</li>
                        </ul>
                    </li>
                    <li><strong>Add age tracks</strong> on the edition: name, age range, capacity, and a <strong>linked product</strong>:
                        <ul class="mt-1 list-disc space-y-1 pl-5">
                            <li>The track&rsquo;s price comes from its linked product&rsquo;s default price. Keep that product&rsquo;s status <strong>Hidden</strong> so it doesn&rsquo;t appear in the public course list.</li>
                            <li>Age ranges must cover everyone you accept, with no gaps &mdash; a child outside every range gets &ldquo;couldn&rsquo;t match an age track&rdquo; and cannot register.</li>
                            <li><strong>Capacity</strong> empty = unlimited. When a numeric capacity fills, that track automatically shows <strong>&ldquo;Join waitlist&rdquo;</strong> instead of Register.</li>
                        </ul>
                    </li>
                </ol>

                <p><strong>Registrations</strong> live under Programs &rarr; Registrations: statuses flow from started &rarr; seat held (45-minute payment hold) &rarr; paid, or &rarr; waitlisted. When a paid seat is refunded/cancelled, waitlisted families are offered the seat in order.</p>
            </div>
        </x-filament::section>

        {{-- ============================== ORDERS & PAYMENTS ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Orders, payments and refunds</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <ul class="list-disc space-y-1 pl-5">
                    <li>Card payments run through <strong>Paystack (live)</strong>. Every attempt creates an <strong>Order</strong> here; successful payment marks it paid, enrolls the learner, and emails a receipt.</li>
                    <li><strong>Refunds are issued from the Paystack dashboard</strong>, not from this admin. The webhook then updates the order automatically: a full refund cancels the order and suspends the enrollment.</li>
                    <li>If a payment succeeded but the order looks unpaid, check Operations &rarr; failed webhook events before assuming the money is missing.</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- ============================== PEOPLE ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Admin users and roles</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <ul class="list-disc space-y-1 pl-5">
                    <li><strong>Roles</strong>: <em>Super Admin</em> and <em>Admin</em> both have full admin access; <em>Learner</em>, <em>Instructor</em> and <em>Corporate</em> get their own portals only.</li>
                    <li>New admin colleagues: create the user, assign the Admin role, and have them set their own password via <strong>&ldquo;Forgot password?&rdquo;</strong> on the login page &mdash; never share passwords over chat.</li>
                    <li>Change your own password from the <strong>profile menu</strong> (top-right avatar).</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- ============================== GOOD TO KNOW ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Good to know: dates, emails, search</x-slot>

            <div class="space-y-4 text-sm leading-6">
                <ul class="list-disc space-y-1 pl-5">
                    <li><strong>All dates and times are WAT</strong> (Africa/Lagos). What you type is what visitors experience.</li>
                    <li><strong>Emails</strong> (receipts, password resets, registration confirmations) are sent from <code>noreply&#64;app.skillupglobal.tech</code> via Resend. If someone reports a missing email, have them check spam first.</li>
                    <li><strong>Search &amp; listings</strong>: the public course list is powered by a search index that updates within a minute of saving. Newly published items appearing &ldquo;late&rdquo; is usually just this delay &mdash; or a future <em>Published at</em> date.</li>
                </ul>
            </div>
        </x-filament::section>

        {{-- ============================== TROUBLESHOOTING ============================== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Troubleshooting quick table</x-slot>

            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-semibold">Symptom</th>
                            <th class="py-2 font-semibold">Fix</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr><td class="py-2 pr-4 align-top">Course missing from the website</td><td class="py-2">Status = Published, <em>Published at</em> not in the future, active default price exists, wait a minute for indexing.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">Program page says &ldquo;Registration is not open yet&rdquo;</td><td class="py-2">Edition status is not <em>Registration open</em>, or the edition has no age tracks yet.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">&ldquo;Join waitlist&rdquo; instead of Register</td><td class="py-2">That track&rsquo;s capacity is full &mdash; raise capacity or leave it as a waitlist.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">&ldquo;Couldn&rsquo;t match an age track&rdquo; / negative age</td><td class="py-2">Edition&rsquo;s <em>Age reference date</em> has the wrong year, or track age ranges don&rsquo;t cover that child&rsquo;s age.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">Track shows no price at registration</td><td class="py-2">The track has no linked product, or its product&rsquo;s default price is missing/inactive.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">Paid but order shows unpaid</td><td class="py-2">Check failed webhook events; confirm the payment in the Paystack dashboard before retrying.</td></tr>
                        <tr><td class="py-2 pr-4 align-top">Reset/receipt email not received</td><td class="py-2">Spam folder first; then confirm the address; repeated failures &mdash; contact the site maintainer.</td></tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
