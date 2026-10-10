/**
 * BEAUTY RESERVE - REPORTS MODULE
 * ------------------------------------------------------------
 * Front-end preview using hardcoded mock data.
 * Automatically connects to MySQL database data when available.
 */

// ================================================================
// 1. HARDCODED MOCK DATA
// ================================================================

const defaultReports = {
    Monthly: {
        dateRange: "OCT 01, 2026 - OCT 31, 2026",
        totalBookings: 142,
        completed: 128,
        cancelled: 14,
        bookingValue: 12850.00,
        paid: 11200.00,
        unpaid: 1650.00,
        beauticians: [
            { name: "Sophia Martinez", subtitle: "48 Bookings", percentage: 85, image: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" },
            { name: "Isabella Chen", subtitle: "36 Bookings", percentage: 65, image: "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80" },
            { name: "Camila Rodriguez", subtitle: "28 Bookings", percentage: 50, image: "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&auto=format&fit=crop&q=80" },
            { name: "Chloe Vance", subtitle: "16 Bookings", percentage: 30, image: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&auto=format&fit=crop&q=80" }
        ],
        clients: [
            { name: "Emma Watson", subtitle: "8 Appointments (PHP 9,500.00)", percentage: 90, image: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&auto=format&fit=crop&q=80" },
            { name: "Olivia Taylor", subtitle: "6 Appointments (PHP 7,200.00)", percentage: 70, image: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" },
            { name: "Mia Hamm", subtitle: "5 Appointments (PHP 5,800.00)", percentage: 55, image: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&auto=format&fit=crop&q=80" },
            { name: "Ava Gardner", subtitle: "4 Appointments (PHP 4,100.00)", percentage: 40, image: "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80" }
        ]
    },
    Weekly: {
        dateRange: "OCT 05, 2026 - OCT 11, 2026",
        totalBookings: 36,
        completed: 31,
        cancelled: 5,
        bookingValue: 3120.00,
        paid: 2800.00,
        unpaid: 320.00,
        beauticians: [
            { name: "Sophia Martinez", subtitle: "14 Bookings", percentage: 90, image: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" },
            { name: "Isabella Chen", subtitle: "10 Bookings", percentage: 65, image: "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80" }
        ],
        clients: [
            { name: "Emma Watson", subtitle: "2 Appointments (PHP 2,400.00)", percentage: 85, image: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&auto=format&fit=crop&q=80" },
            { name: "Olivia Taylor", subtitle: "2 Appointments (PHP 1,900.00)", percentage: 70, image: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" }
        ]
    },
    Daily: {
        dateRange: "OCT 09, 2026",
        totalBookings: 8,
        completed: 7,
        cancelled: 1,
        bookingValue: 680.00,
        paid: 580.00,
        unpaid: 100.00,
        beauticians: [
            { name: "Sophia Martinez", subtitle: "4 Bookings", percentage: 100, image: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" },
            { name: "Isabella Chen", subtitle: "3 Bookings", percentage: 75, image: "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80" }
        ],
        clients: [
            { name: "Mia Hamm", subtitle: "1 Appointment (PHP 1,800.00)", percentage: 100, image: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&auto=format&fit=crop&q=80" }
        ]
    }
};

// Automatically switch to MySQL server dataset when injected
const reportsData = window.SERVER_REPORTS_DATA || defaultReports;


// ================================================================
// 2. MODULE STATE
// ================================================================

let currentPeriod = "Monthly";


// ================================================================
// 3. INITIALIZE PAGE
// ================================================================

document.addEventListener("DOMContentLoaded", function () {
    renderReports();
});


// ================================================================
// 4. RENDER REPORTS DASHBOARD
// ================================================================

function renderReports() {
    const data = reportsData[currentPeriod] || reportsData.Monthly;

    // Update Date Range Pill Display
    const dateRangeEl = document.getElementById("dateRangeDisplay");
    if (dateRangeEl) {
        dateRangeEl.innerText = data.dateRange;
    }

    // Update 6 Stat Cards
    document.getElementById("statTotalBookings").innerText = data.totalBookings;
    document.getElementById("statCompleted").innerText = data.completed;
    document.getElementById("statCancelled").innerText = data.cancelled;

    document.getElementById("statBookingValue").innerText = `PHP ${parseFloat(data.bookingValue).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
    document.getElementById("statPaid").innerText = `PHP ${parseFloat(data.paid).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
    document.getElementById("statUnpaid").innerText = `PHP ${parseFloat(data.unpaid).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

    // Render Per Beautician Progress List
    renderPerformanceList("perBeauticianList", data.beauticians);

    // Render Per Client Progress List
    renderPerformanceList("perClientList", data.clients);
}


// ================================================================
// 5. HELPER RENDER FOR LIST CARDS
// ================================================================

function renderPerformanceList(containerId, list) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!list || list.length === 0) {
        container.innerHTML = `<div class="text-muted small py-3 text-center">No data available for this period.</div>`;
        return;
    }

    container.innerHTML = list.map(item => `
        <div class="performance-item d-flex align-items-center gap-3">
            <img src="${item.image || ''}" alt="${item.name}" class="performance-avatar" onerror="this.src='https://via.placeholder.com/42';">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="performance-title">${item.name}</span>
                    <small class="text-muted fw-semibold" style="font-size: 0.75rem;">${item.subtitle}</small>
                </div>
                <div class="performance-bar-bg">
                    <div class="performance-bar-fill" style="width: ${item.percentage}%;"></div>
                </div>
            </div>
        </div>
    `).join("");
}


// ================================================================
// 6. TIMEFRAME FILTER SWITCHER (Daily, Weekly, Monthly)
// ================================================================

function setPeriod(period, btn) {
    document.querySelectorAll(".filter-pill").forEach(pill => {
        pill.classList.remove("active");
    });

    if (btn) {
        btn.classList.add("active");
    }

    currentPeriod = period;
    renderReports();
}