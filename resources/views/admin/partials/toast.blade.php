<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1085;">
    <div id="appToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="appToastBody"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    (function () {
        window.showToast = function (message, variant = 'success', delay = 3500) {
            const toastEl = document.getElementById('appToast');
            const bodyEl  = document.getElementById('appToastBody');
            if (!toastEl || !bodyEl) return;

            toastEl.className = `toast align-items-center text-bg-${variant} border-0`;
            bodyEl.textContent = message || '';

            const closeBtn = toastEl.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.classList.remove('btn-close-white');
                if (['primary','success','danger','dark'].includes(variant)) closeBtn.classList.add('btn-close-white');
            }

            const t = bootstrap.Toast.getOrCreateInstance(toastEl, { delay });
            t.show();
        };
    })();
</script>

{{-- Session Messages --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success')), 'success'));
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => showToast(@json(session('error')), 'danger'));
    </script>
@endif

{{-- Validation Errors (أول خطأ) --}}
@if(isset($errors) && $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => showToast(@json($errors->first()), 'danger', 5000));
    </script>
@endif
