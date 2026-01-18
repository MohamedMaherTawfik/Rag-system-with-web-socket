<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">Web System</h1>
    <nav>
        <ul id="authNav" class="flex gap-4 items-center">
            <li><a href="/" class="font-bold hover:text-blue-600 transition">Home</a></li>
            <li id="authLoading" class="text-gray-500 text-sm">Loading...</li>
        </ul>
    </nav>
</header>
<script>
    function updateAuthUI() {
        const authNav = document.getElementById('authNav');
        const loadingEl = document.getElementById('authLoading');

        if (!authNav || !loadingEl) return;

        const token = localStorage.getItem('token');
        setTimeout(() => {
            if (loadingEl.parentNode) {
                loadingEl.remove();
            }

            if (token) {
                const profileLi = document.createElement('li');
                const profileLink = document.createElement('a');
                profileLink.href = '/profile';
                profileLink.textContent = 'Profile';
                profileLink.className = 'font-bold hover:text-blue-600 transition';
                profileLi.appendChild(profileLink);

                const logoutLi = document.createElement('li');
                const logoutLink = document.createElement('a');
                logoutLink.href = '#';
                logoutLink.textContent = 'Logout';
                logoutLink.className = 'font-bold hover:text-blue-600 transition';
                logoutLink.addEventListener('click', async (e) => {
                    e.preventDefault();
                    try {
                        await fetch('/api/v1/users/logout', {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Accept': 'application/json'
                            }
                        });
                    } catch (err) {
                        console.error('Logout error:', err);
                    }
                    localStorage.removeItem('token');
                    updateAuthUI();
                    window.location.href = '/';
                });
                logoutLi.appendChild(logoutLink);

                authNav.appendChild(profileLi);
                authNav.appendChild(logoutLi);
            } else {
                const loginLi = document.createElement('li');
                const loginLink = document.createElement('a');
                loginLink.href = '/login';
                loginLink.textContent = 'Login';
                loginLink.className = 'font-bold hover:text-blue-600 transition';
                loginLi.appendChild(loginLink);
                authNav.appendChild(loginLi);
            }
        }, 100);
    }
    document.addEventListener('DOMContentLoaded', updateAuthUI);
</script>
