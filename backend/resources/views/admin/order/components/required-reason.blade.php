{{-- A reason is required to reject, cancel or close an order: the customer
     reads it on their order. Shared by the Review modal and the Close Held
     Order dialog.

     - [data-required-reason] marks the field.
     - requireReason(field) checks it: blank or only spaces shows the error
       under it, focuses it and answers false. The Review modal calls it
       before its scripted submit, which skips the browser's own check.
     - A real submit of a form holding such a field, while it is marked
       `required`, is checked the same way.
     - .order-reason-chip[data-reason-for] fills the named field. --}}
@once
    <style>
        .order-required-pill {
            display: inline-block;
            padding: 0.12rem 0.5rem;
            border-radius: 999px;
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .order-field-input.is-invalid,
        .order-field-input.is-invalid:focus {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12) !important;
        }
    </style>

    <script>
        window.requireReason = function (field) {
            if (!field) return true;

            const filled = field.value.trim() !== '';
            field.classList.toggle('is-invalid', !filled);
            if (!filled) {
                field.value = '';
                field.focus({ preventScroll: true });

                // On a phone the message sits under the fold, behind the
                // modal's sticky buttons; bring it up with the box.
                const message = field.parentElement.querySelector('.invalid-feedback');
                (message || field).scrollIntoView({ block: 'nearest' });
            }

            return filled;
        };

        document.addEventListener('click', function (event) {
            const chip = event.target.closest('.order-reason-chip[data-reason-for]');
            if (!chip) return;

            const field = document.getElementById(chip.dataset.reasonFor);
            if (!field) return;

            field.value = chip.dataset.reason || chip.textContent.trim();
            field.classList.remove('is-invalid');
        });

        document.addEventListener('input', function (event) {
            if (event.target.matches('[data-required-reason]') && event.target.value.trim() !== '') {
                event.target.classList.remove('is-invalid');
            }
        });

        // Only while the field is in play: the Review modal marks its reason
        // required just for the reject step.
        document.addEventListener('submit', function (event) {
            const field = event.target.querySelector('[data-required-reason]');
            if (field && field.required && !window.requireReason(field)) {
                event.preventDefault();
            }
        });
    </script>
@endonce
