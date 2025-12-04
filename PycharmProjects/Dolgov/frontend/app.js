// NetGuardian Auth - Fixed Login Version
document.addEventListener('DOMContentLoaded', function () {
    console.log('NetGuardian Auth System loaded');

    // Инициализация
    initAuthSystem();
});

function initAuthSystem() {
    initTabs();
    initPasswordToggles();
    initPasswordMatch();
    initForms();
    clearMessages();
}

// Переключение вкладок
function initTabs() {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active-form'));

            this.classList.add('active');
            const targetForm = document.getElementById(this.dataset.tab + 'Form');
            if (targetForm) {
                targetForm.classList.add('active-form');
            }

            clearMessages();
            hidePasswordMatch();
        });
    });
}

// Переключатели видимости пароля
function initPasswordToggles() {
    document.addEventListener('click', function (e) {
        if (e.target.closest('.toggle-password')) {
            const button = e.target.closest('.toggle-password');
            const input = button.parentElement.querySelector('.password-input');
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    });
}

// Проверка совпадения паролей
function initPasswordMatch() {
    const passwordInputs = document.querySelectorAll('input[name="password"], input[name="confirm_password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', checkPasswordMatch);
    });
}

function checkPasswordMatch() {
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="confirm_password"]');
    const matchIndicator = document.querySelector('.password-match');

    if (!passwordInput || !confirmInput || !matchIndicator) return;

    const password = passwordInput.value;
    const confirm = confirmInput.value;

    if (!password && !confirm) {
        hidePasswordMatch();
        return;
    }

    if (!confirm) {
        hidePasswordMatch();
        return;
    }

    if (password === confirm) {
        showPasswordMatch('✅ Пароли совпадают', 'success');
    } else {
        showPasswordMatch('❌ Пароли не совпадают', 'error');
    }
}

function showPasswordMatch(message, type) {
    const matchIndicator = document.querySelector('.password-match');
    if (!matchIndicator) return;

    matchIndicator.style.display = 'block';
    matchIndicator.className = `password-match match-${type}`;
    matchIndicator.querySelector('.match-text').textContent = message;
}

function hidePasswordMatch() {
    const matchIndicator = document.querySelector('.password-match');
    if (matchIndicator) {
        matchIndicator.style.display = 'none';
    }
}

// Инициализация форм
function initForms() {
    initLoginForm();
    initRegisterForm();
}

// Форма входа
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) {
        console.error('Форма входа не найдена!');
        return;
    }

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        console.log('Отправка формы входа...');

        const formData = new FormData(this);
        const data = {
            login: formData.get('login'),
            password: formData.get('password')
        };

        // Базовая валидация
        if (!data.login || !data.password) {
            showMessage('Заполните все поля', 'error');
            return;
        }

        await submitForm(this, 'login.php', 'Вход...', data);
    });
}

// Форма регистрации
function initRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;

    registerForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {
            full_name: formData.get('full_name'),
            email: formData.get('email'),
            login_username: formData.get('login_username'),
            password: formData.get('password'),
            confirm_password: formData.get('confirm_password')
        };

        // Валидация
        if (!data.full_name || !data.email || !data.login_username || !data.password || !data.confirm_password) {
            showMessage('Все поля обязательны для заполнения', 'error');
            return;
        }

        if (data.password !== data.confirm_password) {
            showMessage('Пароли не совпадают', 'error');
            return;
        }

        if (data.password.length < 8) {
            showMessage('Пароль должен содержать минимум 8 символов', 'error');
            return;
        }

        if (data.login_username.length < 3) {
            showMessage('Логин должен содержать минимум 3 символа', 'error');
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(data.login_username)) {
            showMessage('Логин может содержать только латинские буквы, цифры и подчеркивание', 'error');
            return;
        }

        await submitForm(this, 'register.php', 'Регистрация...', data);
    });
}

// Общая функция отправки формы
async function submitForm(form, url, loadingText, data) {
    const button = form.querySelector('button[type="submit"]');
    const btnText = button.querySelector('.btn-text');
    const originalText = btnText.textContent;

    // Показываем состояние загрузки
    btnText.textContent = loadingText;
    button.disabled = true;

    try {
        console.log('Отправка данных на', url, ':', data);

        // Отправляем запрос
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        console.log('Статус ответа:', response.status);
        console.log('Заголовки ответа:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed result:', result);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response that failed to parse:', responseText);
            throw new Error('Неверный ответ сервера');
        }

        // Обрабатываем результат
        if (result.success) {
            showMessage(result.message, 'success');

            if (url === 'login.php') {
                console.log('Вход успешен, перенаправление...');
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else if (url === 'register.php') {
                setTimeout(() => {
                    const loginTab = document.querySelector('.tab[data-tab="login"]');
                    if (loginTab) {
                        loginTab.click();
                    }
                    form.reset();
                    hidePasswordMatch();
                }, 2000);
            }
        } else {
            showMessage(result.message, 'error');
        }

    } catch (error) {
        console.error('Request error:', error);
        showMessage('Ошибка сети: ' + error.message, 'error');
    } finally {
        // Восстанавливаем кнопку
        btnText.textContent = originalText;
        button.disabled = false;
    }
}

// Показать сообщение
function showMessage(message, type) {
    clearMessages();

    const messageDiv = document.createElement('div');
    messageDiv.className = `auth-message ${type}`;
    messageDiv.textContent = message;

    const authHeader = document.querySelector('.auth-header');
    if (authHeader && authHeader.parentNode) {
        authHeader.parentNode.insertBefore(messageDiv, authHeader.nextSibling);
    }

    setTimeout(clearMessages, 5000);
}

// Очистить сообщения
function clearMessages() {
    const messages = document.querySelectorAll('.auth-message');
    messages.forEach(msg => {
        if (msg.parentNode) {
            msg.parentNode.removeChild(msg);
        }
    });
}