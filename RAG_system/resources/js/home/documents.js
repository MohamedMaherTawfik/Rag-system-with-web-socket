import { API_KEY } from "../constants/consts.js";

const apiKey = localStorage.getItem("token");

async function fetchDocuments() {
    const container = document.getElementById("documentsContainer");
    container.innerHTML = `<p class="text-gray-500">Loading documents...</p>`;

    if (!apiKey) {
        container.innerHTML = `<p class="text-red-500">Please login to view documents.</p>`;
        return;
    }

    try {
        const res = await fetch("/api/v1/users/documents", {
            headers: {
                Authorization: "Bearer " + apiKey,
                Accept: "application/json",
                "x-api-key": API_KEY,
            },
        });

        if (!res.ok) throw new Error("Failed to fetch documents");

        const data = await res.json();

        if (!data.data || data.data.length === 0) {
            container.innerHTML = `<p class="text-gray-500">No documents found.</p>`;
            return;
        }

        container.innerHTML = "";

        data.data.forEach((doc) => {
            const sizeKB = (doc.size / 1024).toFixed(1);
            const createdAt = new Date(doc.created_at).toLocaleString();

            const fileUrl = `/storage/pdfs/${doc.filename}`;

            const card = document.createElement("div");
            card.className =
                "bg-white shadow p-4 rounded-lg flex flex-col justify-between hover:shadow-lg transition";

            card.innerHTML = `
                <h3 class="font-bold text-lg mb-2 truncate">${doc.title || "Untitled"}</h3>
                <p class="text-gray-500 text-sm mb-2">Size: ${sizeKB} KB</p>
                <p class="text-gray-500 text-sm mb-4">Uploaded: ${createdAt}</p>
                <div class="mt-auto flex gap-2">
                    <a href="${fileUrl}" target="_blank"
                       class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                       View / Download
                    </a>
                </div>
            `;

            container.appendChild(card);
        });
    } catch (err) {
        console.error("Error fetching documents:", err);
        container.innerHTML = `<p class="text-red-500">Failed to load documents.</p>`;
    }
}

document.addEventListener("DOMContentLoaded", fetchDocuments);
