<?php
/**
 * Mortgage Form Block Render
 * Uses WordPress Interactivity API with nested contexts
 */

$page_type = $attributes['pageType'] ?? 'loan-app';
$is_rate_quote = $page_type === 'rate-quote';

// Add CSS for Interactivity API
wp_add_inline_style('wp-block-library', '.hidden { display: none !important; }');

// Get loan officer data
$owner_id = get_post_field('post_author', get_the_ID());
$user = get_user_by('id', $owner_id);

// Get Person CPT data
$person_data = array();
if (class_exists('FRS_ACF_Fields')) {
    $person_data = FRS_ACF_Fields::get_loan_officer_person($owner_id);
}

$lo_name = $person_data['name'] ?? $user->display_name ?? 'Your Loan Officer';

$context = array(
    'step' => 1,
    'goal' => '',
    'bestTime' => '',
    'pageId' => get_the_ID(),
    'loanOfficerId' => $owner_id,
    'template' => $page_type,
);
?>

<div
    <?php echo get_block_wrapper_attributes(); ?>
    data-wp-interactive="frs-mortgage-landing"
    <?php echo wp_interactivity_data_wp_context($context); ?>
>
    <section class="py-16 px-6 bg-white">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-2xl p-8 md:p-12">

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600">
                            Step <span data-wp-text="context.step"></span> of 3
                        </span>
                        <span class="text-sm font-semibold text-blue-600" data-wp-text="state.progressPercentage"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            data-wp-style--width="state.progressWidth"
                        ></div>
                    </div>
                </div>

                <!-- Form -->
                <form data-wp-on--submit="actions.handleSubmit">

                    <!-- Step 1: Goal Selection -->
                    <div data-wp-class--hidden="!state.isStep1">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">
                            <?php echo $is_rate_quote ? "Let's Find Your Best Rate" : "Let's Get Started"; ?>
                        </h3>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">What's your primary goal?</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php if ($is_rate_quote):
                                    $goals = array(
                                        array('value' => 'purchase', 'title' => 'Purchase a Home', 'desc' => 'I\'m buying a new property'),
                                        array('value' => 'refinance', 'title' => 'Refinance', 'desc' => 'Lower my current rate'),
                                    );
                                else:
                                    $goals = array(
                                        array('value' => 'first-time', 'title' => 'First-Time Buyer', 'desc' => 'Buying my first home'),
                                        array('value' => 'move-up', 'title' => 'Move-Up Buyer', 'desc' => 'Upgrading to a new home'),
                                        array('value' => 'investment', 'title' => 'Investment Property', 'desc' => 'Buying to rent or flip'),
                                        array('value' => 'refinance', 'title' => 'Refinance', 'desc' => 'Improve my current loan'),
                                    );
                                endif;

                                foreach ($goals as $goal): ?>
                                    <button
                                        type="button"
                                        data-goal-value="<?php echo esc_attr($goal['value']); ?>"
                                        class="w-full border-2 border-gray-300 hover:border-blue-600 rounded-lg p-4 text-left hover:bg-blue-50 transition"
                                        data-wp-on--click="actions.selectGoal"
                                    >
                                        <div class="font-semibold text-gray-900"><?php echo esc_html($goal['title']); ?></div>
                                        <div class="text-sm text-gray-600"><?php echo esc_html($goal['desc']); ?></div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="w-full mt-6 bg-blue-600 text-white py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition"
                            data-wp-on--click="actions.nextStep"
                        >
                            Continue
                        </button>
                    </div>

                    <!-- Step 2: Property Information -->
                    <div data-wp-class--hidden="!state.isStep2">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Property Information</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Property Location (ZIP Code)</label>
                                <input type="text" name="propertyZip" placeholder="94102" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Estimated Home Price</label>
                                <input type="text" name="homePrice" placeholder="$500,000" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Down Payment Amount</label>
                                <input type="text" name="downPayment" placeholder="$100,000" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Property Type</label>
                                <select name="propertyType" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none">
                                    <option value="single-family">Single Family Home</option>
                                    <option value="condo">Condo/Townhouse</option>
                                    <option value="multi-family">Multi-Family (2-4 units)</option>
                                    <option value="investment">Investment Property</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button type="button" class="flex-1 border-2 border-gray-300 text-gray-700 py-4 rounded-lg font-semibold hover:bg-gray-50 transition" data-wp-on--click="actions.prevStep">Back</button>
                            <button type="button" class="flex-1 bg-blue-600 text-white py-4 rounded-lg font-semibold hover:bg-blue-700 transition" data-wp-on--click="actions.nextStep">Continue</button>
                        </div>
                    </div>

                    <!-- Step 3: Contact Information -->
                    <div data-wp-class--hidden="!state.isStep3">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Your Contact Information</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                                <input type="text" name="firstName" required placeholder="John" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                                <input type="text" name="lastName" required placeholder="Doe" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                                <input type="email" name="email" required placeholder="john@example.com" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required placeholder="(555) 123-4567" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none" />
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Best Time to Contact</label>
                            <div class="grid grid-cols-3 gap-4">
                                <?php foreach (array('morning', 'afternoon', 'evening') as $time): ?>
                                    <button
                                        type="button"
                                        data-time-value="<?php echo esc_attr($time); ?>"
                                        class="w-full border-2 border-gray-300 hover:border-blue-600 rounded-lg py-3 hover:bg-blue-50 transition"
                                        data-wp-on--click="actions.selectTime"
                                    >
                                        <?php echo ucfirst($time); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button type="button" class="flex-1 border-2 border-gray-300 text-gray-700 py-4 rounded-lg font-semibold hover:bg-gray-50 transition" data-wp-on--click="actions.prevStep">Back</button>
                            <button type="submit" class="flex-1 bg-green-600 text-white py-4 rounded-lg font-semibold hover:bg-green-700 transition">Submit Application</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>
