@if (! empty($deliveryReviewPrompt))
<div id="delivery-review-modal" class="dr-modal" hidden>
    <div class="dr-modal__backdrop" data-dr-later></div>
    <div class="dr-modal__panel" role="dialog" aria-modal="true" aria-labelledby="dr-title">
        <div class="dr-step" data-dr-step="delivered">
            <div class="dr-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
            <p class="dr-kicker">Step 1 of 3</p>
            <h2 id="dr-title" class="font-heading">Your product was delivered successfully</h2>
            <p>Order <strong>#{{ $deliveryReviewPrompt['order_number'] }}</strong> has reached you. Would you like to rate your jewellery?</p>
            <div class="dr-actions">
                <button type="button" class="account-solid-btn" data-dr-next>Write a review</button>
                <button type="button" class="account-ghost-btn account-ghost-btn--small" data-dr-later>Later</button>
            </div>
        </div>

        <div class="dr-step" data-dr-step="review" hidden>
            <p class="dr-kicker" data-dr-progress>Step 2 of 3</p>
            <div class="dr-product">
                <img data-dr-image alt="">
                <h3 class="font-heading" data-dr-name></h3>
            </div>
            <form id="delivery-review-form" class="account-form">
                @csrf
                <input type="hidden" name="order_id" value="{{ $deliveryReviewPrompt['order_id'] }}">
                <input type="hidden" name="product_id" value="">
                <fieldset class="account-star-pick">
                    <legend>How would you rate this?</legend>
                    @for ($star = 5; $star >= 1; $star--)
                        <input type="radio" name="rating" id="dr-star-{{ $star }}" value="{{ $star }}" required>
                        <label for="dr-star-{{ $star }}" title="{{ $star }} star{{ $star > 1 ? 's' : '' }}">{{ $star }}</label>
                    @endfor
                </fieldset>
                <p class="dr-error" data-dr-error hidden></p>
                <div class="dr-actions">
                    <button type="submit" class="account-solid-btn">Submit rating</button>
                    <button type="button" class="account-ghost-btn account-ghost-btn--small" data-dr-later>Later</button>
                </div>
            </form>
        </div>

        <div class="dr-step" data-dr-step="thanks" hidden>
            <div class="dr-icon" aria-hidden="true"><i class="bi bi-heart"></i></div>
            <p class="dr-kicker">Step 3 of 3</p>
            <h2 class="font-heading">Thank you</h2>
            <p data-dr-thanks>Your review is with our team. It will appear on the product after approval.</p>
            <div class="dr-actions">
                <button type="button" class="account-solid-btn" data-dr-close>Done</button>
            </div>
        </div>
    </div>
</div>
@endif
