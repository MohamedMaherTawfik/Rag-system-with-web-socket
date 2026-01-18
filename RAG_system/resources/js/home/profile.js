import { API_KEY, WS_URL } from "../constants/consts.js";
document.addEventListener("DOMContentLoaded", async () => {
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/login";
        return;
    }

    const profileInfo = document.getElementById("profileInfo");
    const updateForm = document.getElementById("updateProfileForm");
    const memoryToggle = document.getElementById("memory_enabled");
    const updateMessage = document.getElementById("updateMessage");

    try {
        const res = await fetch("/api/v1/users/profile", {
            headers: {
                Authorization: "Bearer " + token,
                "x-api-key": API_KEY,
            },
        });
        const data = await res.json();

        if (res.ok) {
            const user = data.data;
            profileInfo.innerHTML = `
                <p><strong>الاسم:</strong> ${user.name}</p>
                <p><strong>البريد الإلكتروني:</strong> ${user.email}</p>
                <p><strong>الصلاحية:</<strong> ${user.role || "—"}</p>
                <p><strong>آخر تسجيل دخول:</strong> ${
                    user.last_login_at
                        ? new Date(user.last_login_at).toLocaleString("ar-EG", {
                              timeZone: "Africa/Cairo",
                              dateStyle: "full",
                              timeStyle: "short",
                          })
                        : "—"
                }</p>
                <p><strong>الذاكرة المؤقتة:</strong> ${
                    user.memory_enabled ? "مفعلة" : "معطلة"
                }</p>
            `;
            memoryToggle.checked = user.memory_enabled;
        } else {
            profileInfo.innerHTML =
                '<p class="text-red-500">فشل تحميل البيانات</p>';
        }
    } catch (err) {
        console.error(err);
        profileInfo.innerHTML = '<p class="text-red-500">خطأ في الاتصال</p>';
    }

    updateForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        updateMessage.textContent = "";
        updateMessage.className = "mt-4 min-h-[1.5rem] text-sm";

        const formData = {
            memory_enabled: memoryToggle.checked ? 1 : 0,
        };

        try {
            const res = await fetch("/api/v1/users/update-profile", {
                method: "PATCH",
                headers: {
                    Authorization: "Bearer " + token,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "x-api-key": API_KEY,
                },
                body: JSON.stringify(formData),
            });

            const result = await res.json();

            if (res.ok) {
                updateMessage.textContent = "تم الحفظ بنجاح!";
                updateMessage.classList.add("text-green-500");
                setTimeout(() => location.reload(), 1000);
            } else {
                updateMessage.textContent = result.message || "فشل الحفظ";
                updateMessage.classList.add("text-red-500");
            }
        } catch (err) {
            console.error(err);
            updateMessage.textContent = "حدث خطأ أثناء الحفظ";
            updateMessage.classList.add("text-red-500");
        }
    });
});
