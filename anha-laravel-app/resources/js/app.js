import {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    BarChart3,
    Box,
    CheckCircle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    ClipboardList,
    Database,
    DollarSign,
    Eye,
    FileText,
    Home,
    Image,
    LayoutGrid,
    List,
    LockKeyhole,
    LogIn,
    LogOut,
    Mail,
    Map,
    Megaphone,
    Menu,
    MessageSquareMore,
    MessageSquareReply,
    MessagesSquare,
    Package,
    Pencil,
    Phone,
    Plus,
    Power,
    Save,
    Scale,
    Search,
    Send,
    Settings,
    Settings2,
    ShieldCheck,
    ShoppingBag,
    SlidersHorizontal,
    Star,
    Ticket,
    ToggleLeft,
    ToggleRight,
    Trash2,
    TrendingUp,
    Truck,
    Upload,
    UserCircle,
    UserPlus,
    UserRound,
    Users,
    Users2,
    X,
    XCircle,
    Zap,
    createIcons,
} from "lucide";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const icons = {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    BarChart3,
    Box,
    CheckCircle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    ClipboardList,
    Database,
    DollarSign,
    Eye,
    FileText,
    Home,
    Image,
    LayoutGrid,
    List,
    LockKeyhole,
    LogIn,
    LogOut,
    Mail,
    Map,
    Megaphone,
    Menu,
    MessageSquareMore,
    MessageSquareReply,
    MessagesSquare,
    Package,
    Pencil,
    Phone,
    Plus,
    Power,
    Save,
    Scale,
    Search,
    Send,
    Settings,
    Settings2,
    ShieldCheck,
    ShoppingBag,
    SlidersHorizontal,
    Star,
    Ticket,
    ToggleLeft,
    ToggleRight,
    Trash2,
    TrendingUp,
    Truck,
    Upload,
    UserCircle,
    UserPlus,
    UserRound,
    Users,
    Users2,
    X,
    XCircle,
    Zap,
};

const ready = (callback) => {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", callback);
    } else {
        callback();
    }
};

const debounce = (fn, delay = 300) => {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const selectedFilesByInput = new WeakMap();

const convertImageToWebp = async (file) => {
    if (!file.type.startsWith("image/") || file.type === "image/webp") {
        return file;
    }

    const bitmap = await createImageBitmap(file);
    const canvas = document.createElement("canvas");
    canvas.width = bitmap.width;
    canvas.height = bitmap.height;
    const context = canvas.getContext("2d");
    context.drawImage(bitmap, 0, 0);

    const blob = await new Promise((resolve, reject) => {
        canvas.toBlob(
            (value) =>
                value
                    ? resolve(value)
                    : reject(new Error("Gagal mengonversi gambar.")),
            "image/webp",
            0.85,
        );
    });

    const name = file.name.replace(/\.[^.]+$/, "") + ".webp";
    return new File([blob], name, {
        type: "image/webp",
        lastModified: Date.now(),
    });
};

const updateInputFiles = (input, files) => {
    const dataTransfer = new DataTransfer();
    files.forEach((file) => dataTransfer.items.add(file));
    input.files = dataTransfer.files;
};

const renderPhotoPickerPreviews = (input) => {
    const target = document.querySelector(input.dataset.previewTarget);
    if (!target) return;

    target
        .querySelectorAll("[data-new-photo-preview]")
        .forEach((preview) => preview.remove());

    const files = selectedFilesByInput.get(input) || [];
    files.forEach((file, index) => {
        const wrapper = document.createElement("div");
        wrapper.className = "relative";
        wrapper.dataset.newPhotoPreview = "true";

        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        img.className =
            "h-20 w-20 rounded-lg border border-border object-cover";

        img.addEventListener(
            "load",
            () => {
                URL.revokeObjectURL(img.src);
            },
            { once: true },
        );

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className =
            "absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-white shadow";
        removeButton.title = "Hapus foto";
        removeButton.setAttribute(
            "aria-label",
            `Hapus ${file.name || "foto produk"}`,
        );
        removeButton.innerHTML = '<i data-lucide="x" class="h-3.5 w-3.5"></i>';
        removeButton.addEventListener("click", () => {
            const next = (selectedFilesByInput.get(input) || []).filter(
                (_, fileIndex) => fileIndex !== index,
            );
            selectedFilesByInput.set(input, next);
            updateInputFiles(input, next);
            renderPhotoPickerPreviews(input);
            createIcons({ icons, attrs: { "stroke-width": 2 } });
        });

        wrapper.append(img, removeButton);
        target.appendChild(wrapper);
    });
};

ready(() => {
    createIcons({
        icons,
        attrs: {
            "stroke-width": 2,
        },
    });

    const sidebar = document.querySelector("[data-sidebar]");
    const sidebarBackdrop = document.querySelector("[data-sidebar-backdrop]");

    document.querySelectorAll("[data-sidebar-toggle]").forEach((button) => {
        button.addEventListener("click", () => {
            sidebar?.classList.toggle("-translate-x-full");
            sidebarBackdrop?.classList.toggle("hidden");
        });
    });

    sidebarBackdrop?.addEventListener("click", () => {
        sidebar?.classList.add("-translate-x-full");
        sidebarBackdrop.classList.add("hidden");
    });

    sidebar?.querySelectorAll("nav a").forEach((link) => {
        link.addEventListener("click", () => {
            if (window.matchMedia("(max-width: 1023px)").matches) {
                sidebar.classList.add("-translate-x-full");
                sidebarBackdrop?.classList.add("hidden");
            }
        });
    });

    const confirmModal = document.querySelector("[data-confirm-modal]");
    const confirmTitle = confirmModal?.querySelector("[data-confirm-title]");
    const confirmMessage = confirmModal?.querySelector(
        "[data-confirm-message]",
    );
    const confirmAccept = confirmModal?.querySelector("[data-confirm-accept]");
    const confirmCancel = confirmModal?.querySelector("[data-confirm-cancel]");
    let pendingConfirmForm = null;

    const closeConfirmModal = () => {
        confirmModal?.classList.add("hidden");
        confirmModal?.classList.remove("flex");
        confirmModal?.setAttribute("aria-hidden", "true");
        pendingConfirmForm = null;
    };

    const openConfirmModal = (form) => {
        pendingConfirmForm = form;
        if (confirmTitle) {
            confirmTitle.textContent =
                form.getAttribute("data-confirm-title") ||
                "Konfirmasi tindakan";
        }
        if (confirmMessage) {
            confirmMessage.textContent =
                form.getAttribute("data-confirm") || "Lanjutkan tindakan ini?";
        }
        if (confirmAccept) {
            confirmAccept.textContent =
                form.getAttribute("data-confirm-accept") || "Ya, lanjutkan";
        }

        confirmModal?.classList.remove("hidden");
        confirmModal?.classList.add("flex");
        confirmModal?.setAttribute("aria-hidden", "false");
        confirmCancel?.focus();
    };

    document.querySelectorAll("form[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!confirmModal) return;

            if (form.dataset.confirmed === "true") {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            openConfirmModal(form);
        });
    });

    confirmAccept?.addEventListener("click", () => {
        if (!pendingConfirmForm) {
            closeConfirmModal();
            return;
        }

        const form = pendingConfirmForm;
        closeConfirmModal();
        form.dataset.confirmed = "true";
        form.requestSubmit();
    });

    confirmCancel?.addEventListener("click", closeConfirmModal);
    confirmModal?.addEventListener("click", (event) => {
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
    });
    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            confirmModal &&
            !confirmModal.classList.contains("hidden")
        ) {
            closeConfirmModal();
        }
    });

    const userDetailModal = document.getElementById("user-detail-modal");
    const userDetailLoading = document.getElementById("user-detail-loading");
    const userDetailContent = document.getElementById("user-detail-content");
    const userDetailClose = document.getElementById("user-detail-close");
    const userDetailMaps = [];

    const escapeHtml = (value) =>
        String(value ?? "-").replace(
            /[&<>"']/g,
            (char) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                })[char],
        );

    const closeUserDetailModal = () => {
        userDetailModal?.classList.add("hidden");
        userDetailModal?.classList.remove("flex");
        userDetailModal?.setAttribute("aria-hidden", "true");
        userDetailMaps.forEach((map) => map.remove());
        userDetailMaps.length = 0;
    };

    const roleLabelsForDetail = { admin: "Admin", ritel: "Ritel", grosir: "Grosir" };

    const renderUserDetail = (user) => {
        const alamats = Array.isArray(user.alamats) ? user.alamats : [];

        const addressCards = alamats.length
            ? alamats
                  .map((alamat, index) => {
                      const hasPoint = alamat.latitude && alamat.longitude;
                      return `
                        <div class="rounded-lg border border-border p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="badge badge-secondary">${escapeHtml(alamat.label)}</span>
                                    ${alamat.default ? '<span class="badge badge-secondary ml-1">Default</span>' : ""}
                                    <p class="mt-1 text-sm font-medium">${escapeHtml(alamat.recipient_name)} &middot; ${escapeHtml(alamat.phone)}</p>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">
                                ${escapeHtml(alamat.address)}${alamat.district ? ", " + escapeHtml(alamat.district) : ""}, ${escapeHtml(alamat.city)}, ${escapeHtml(alamat.province)} ${escapeHtml(alamat.postal_code)}
                            </p>
                            ${
                                hasPoint
                                    ? `<div class="mt-3 h-48 w-full overflow-hidden rounded-lg" data-address-map data-lat="${alamat.latitude}" data-lng="${alamat.longitude}" data-map-index="${index}"></div>`
                                    : '<p class="mt-3 text-xs italic text-muted-foreground">Titik lokasi belum diisi.</p>'
                            }
                        </div>
                    `;
                  })
                  .join("")
            : '<p class="text-sm text-muted-foreground">Belum ada alamat tersimpan.</p>';

        userDetailContent.innerHTML = `
            <div class="rounded-lg border border-border p-3">
                <p class="text-sm font-semibold">${escapeHtml(user.name)}</p>
                <p class="text-sm text-muted-foreground">${escapeHtml(user.email)} &middot; ${escapeHtml(user.phone)}</p>
                <p class="mt-1 text-sm text-muted-foreground">${escapeHtml(user.address)}</p>
                <span class="badge badge-secondary mt-2 inline-block">${roleLabelsForDetail[user.role] ?? escapeHtml(user.role)}</span>
            </div>
            <div>
                <h3 class="mb-2 text-sm font-semibold">Alamat Tersimpan</h3>
                <div class="space-y-3">${addressCards}</div>
            </div>
        `;

        userDetailContent
            .querySelectorAll("[data-address-map]")
            .forEach((element) => {
                const lat = Number(element.dataset.lat);
                const lng = Number(element.dataset.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                const map = L.map(element).setView([lat, lng], 16);
                L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "&copy; OpenStreetMap contributors",
                }).addTo(map);
                L.marker([lat, lng]).addTo(map);
                userDetailMaps.push(map);
            });
    };

    document.querySelectorAll("[data-user-detail-trigger]").forEach((trigger) => {
        trigger.addEventListener("click", async () => {
            if (!userDetailModal) return;

            userDetailModal.classList.remove("hidden");
            userDetailModal.classList.add("flex");
            userDetailModal.setAttribute("aria-hidden", "false");
            userDetailLoading?.classList.remove("hidden");
            userDetailContent?.classList.add("hidden");

            try {
                const response = await fetch(
                    `/sistem/pengguna/${trigger.dataset.userId}/detail`,
                    { headers: { Accept: "application/json" } },
                );
                const json = await response.json();

                if (!response.ok || !json.success) {
                    throw new Error(json.message || "Gagal memuat detail pengguna.");
                }

                userDetailLoading?.classList.add("hidden");
                userDetailContent?.classList.remove("hidden");
                renderUserDetail(json.data);
                createIcons({ icons, attrs: { "stroke-width": 2 } });
            } catch (error) {
                if (userDetailLoading) {
                    userDetailLoading.textContent =
                        error.message || "Gagal memuat detail pengguna.";
                }
            }
        });
    });

    userDetailClose?.addEventListener("click", closeUserDetailModal);
    userDetailModal?.addEventListener("click", (event) => {
        if (event.target === userDetailModal) {
            closeUserDetailModal();
        }
    });
    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            userDetailModal &&
            !userDetailModal.classList.contains("hidden")
        ) {
            closeUserDetailModal();
        }
    });

    document.querySelectorAll("form[data-loading-label]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (event.defaultPrevented) return;

            const submitter = form.querySelector('[type="submit"]');
            if (submitter) {
                submitter.dataset.originalText = submitter.textContent;
                submitter.textContent = form.getAttribute("data-loading-label");
                submitter.setAttribute("disabled", "disabled");
            }
        });
    });

    document
        .querySelectorAll('input[type="file"][data-preview-target]')
        .forEach((input) => {
            input.addEventListener("change", async () => {
                if (input.hasAttribute("data-photo-picker")) return;

                const target = document.querySelector(
                    input.dataset.previewTarget,
                );
                const files = Array.from(input.files || []);
                if (!target) return;

                target.innerHTML = "";
                for (const file of files) {
                    const img = document.createElement("img");
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    img.className =
                        "h-20 w-20 rounded-lg border border-border object-cover";
                    target.appendChild(img);
                }
            });
        });

    document
        .querySelectorAll("[data-remove-existing-photo]")
        .forEach((button) => {
            button.addEventListener("click", () => {
                button.closest("[data-existing-photo]")?.remove();
            });
        });

    document.querySelectorAll("[data-rating-picker]").forEach((picker) => {
        const input = picker.querySelector("[data-rating-input]");
        const stars = Array.from(picker.querySelectorAll("[data-rating-star]"));
        const paint = (value) => {
            stars.forEach((button) => {
                const starValue = Number(button.dataset.ratingValue || 0);
                const icon = button.querySelector("svg, i");
                const active = starValue <= value;
                button.setAttribute(
                    "aria-checked",
                    starValue === value ? "true" : "false",
                );
                icon?.classList.toggle("fill-amber-400", active);
                icon?.classList.toggle("text-amber-400", active);
                icon?.classList.toggle("text-muted-foreground", !active);
            });
        };

        stars.forEach((button) => {
            button.addEventListener("click", () => {
                const value = Number(button.dataset.ratingValue || 0);
                if (input) input.value = String(value);
                paint(value);
            });
        });
    });

    document
        .querySelectorAll('input[type="file"][data-webp]')
        .forEach((input) => {
            input.addEventListener("change", async () => {
                if (
                    !("DataTransfer" in window) ||
                    !("createImageBitmap" in window)
                )
                    return;

                const files = Array.from(input.files || []);
                if (files.length === 0) return;

                const submitter = input
                    .closest("form")
                    ?.querySelector('[type="submit"]');
                submitter?.setAttribute("disabled", "disabled");
                try {
                    const converted = await Promise.all(
                        files.map(convertImageToWebp),
                    );

                    if (input.hasAttribute("data-append-files")) {
                        const next = [
                            ...(selectedFilesByInput.get(input) || []),
                            ...converted,
                        ];
                        selectedFilesByInput.set(input, next);
                        updateInputFiles(input, next);
                        renderPhotoPickerPreviews(input);
                        createIcons({ icons, attrs: { "stroke-width": 2 } });
                        return;
                    }

                    updateInputFiles(input, converted);
                } catch (error) {
                    console.warn(error);
                } finally {
                    submitter?.removeAttribute("disabled");
                }
            });
        });

    document.querySelectorAll("[data-search-select]").forEach((container) => {
        const input = container.querySelector("[data-search-input]");
        const hidden = container.querySelector("[data-search-value]");
        const results = container.querySelector("[data-search-result]");
        const url = container.dataset.url;

        if (!input || !hidden || !results || !url) {
            return;
        }

        let controller = null;

        const performSearch = async (query = "") => {
            controller?.abort();
            controller = new AbortController();

            try {
                const searchUrl = `${url}${url.includes("?") ? "&" : "?"}q=${encodeURIComponent(query)}`;
                const response = await fetch(searchUrl, {
                    signal: controller.signal,
                    headers: {
                        Accept: "application/json",
                    },
                });

                if (!response.ok) {
                    throw new Error("Request failed");
                }

                const items = await response.json();

                results.innerHTML = "";

                if (!Array.isArray(items) || !items.length) {
                    results.innerHTML = `
                        <li class="px-4 py-2.5 text-xs text-muted-foreground">
                            Tidak ada hasil ditemukan
                        </li>
                    `;
                } else {
                    items.forEach((item) => {
                        const li = document.createElement("li");
                        li.className =
                            "cursor-pointer border-b border-border/50 px-4 py-2.5 text-sm transition hover:bg-muted/70 last:border-b-0";
                        li.textContent = item.name;

                        li.addEventListener("click", () => {
                            input.value = item.name;
                            hidden.value = String(item.id);
                            results.classList.add("hidden");
                        });

                        results.appendChild(li);
                    });
                }
                results.classList.remove("hidden");
            } catch (error) {
                if (error.name === "AbortError") {
                    return;
                }
                console.error(error);
            }
        };

        const debouncedSearch = debounce(() => {
            const keyword = input.value.trim();
            performSearch(keyword);
        }, 250);

        input.addEventListener("input", debouncedSearch);

        input.addEventListener("focus", () => {
            const keyword = input.value.trim();
            performSearch(keyword);
        });

        document.addEventListener("click", (event) => {
            if (!container.contains(event.target)) {
                results.classList.add("hidden");
            }
        });
    });

    // <div class="search-select relative w-80"
    //      data-url="{{ route('users.search') }}">
    //     <input type="text" class="search-input ...">
    //     <input type="hidden" name="user_id" class="search-value">
    //     <ul class="search-results ..."></ul>
    // </div>
    //
    // <div class="search-select relative w-80"
    //      data-url="{{ route('categories.search') }}">
    //     <input type="text" class="search-input ...">
    //     <input type="hidden" name="category_id" class="search-value">
    //     <ul class="search-results ..."></ul>
    // </div>
    // Even more reusable
    //
    // You can wrap the markup in a Blade component so you only write it once.
    //
    // resources/views/components/search-select.blade.php
    //
    // <div class="search-select relative w-full" data-url="{{ $url }}">
    //     <input
    //         type="text"
    //         class="search-input w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200"
    //         placeholder="{{ $placeholder }}"
    //         autocomplete="off"
    //     >
    //
    //     <input
    //         type="hidden"
    //         name="{{ $name }}"
    //         class="search-value"
    //     >
    //
    //     <ul
    //         class="search-results absolute z-10 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
    //     ></ul>
    // </div>
    //
    // Then use it like this:
    //
    // <x-search-select
    //     name="user_id"
    //     :url="route('users.search')"
    //     placeholder="Search user..."
    // />
    //
    // <x-search-select
    //     name="product_id"
    //     :url="route('products.search')"
    //     placeholder="Search product..."
    // /></input>
});
