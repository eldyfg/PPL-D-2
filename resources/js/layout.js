const sidebar = document.getElementById('app-sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggle = document.getElementById('sidebar-toggle');
const toastContainer = document.getElementById('toast-container');

const showToast = (message, type = 'success') => {
    if (!toastContainer) return;

    const success = type === 'success';
    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl toast-enter ${
        success
            ? 'border-emerald-200 bg-white text-emerald-800'
            : 'border-rose-200 bg-white text-rose-800'
    }`;
    toast.setAttribute('role', success ? 'status' : 'alert');
    toast.innerHTML = `
        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ${
            success ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'
        }">
            ${success
                ? '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>'
                : '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>'}
        </span>
        <p class="toast-message pt-1 text-sm font-semibold"></p>
    `;
    toast.querySelector('.toast-message').textContent = message;
    toastContainer.append(toast);

    window.setTimeout(() => {
        toast.classList.replace('toast-enter', 'toast-leave');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }, 4000);
};

window.showToast = showToast;

try {
    const pendingToast = sessionStorage.getItem('app-toast');
    if (pendingToast) {
        sessionStorage.removeItem('app-toast');
        const { message, type } = JSON.parse(pendingToast);
        showToast(message, type);
    }
} catch {
    sessionStorage.removeItem('app-toast');
}

if (sidebar && backdrop && toggle) {
    const setSidebarState = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setSidebarState(sidebar.classList.contains('-translate-x-full')));
    backdrop.addEventListener('click', () => setSidebarState(false));
}
