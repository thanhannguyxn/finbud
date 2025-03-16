let monthlyChart; // Declare the chart variable globally

// Function to fetch and update the chart
function fetchAndRenderChart(url) {
    fetch(url)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const chartData = data.data;
            
            // Extract data for the chart
            const months = chartData.map(item => item.month);
            const totalIncome = chartData.map(item => item.total_income);
            const totalExpense = chartData.map(item => item.total_expense);
            const netBalance = chartData.map(item => item.total_income - item.total_expense);
            
            // Check if there is data
            if (months.length === 0) {
                document.getElementById('monthlyExpenseChart').style.display = 'none';
                document.querySelector('.chart-section').insertAdjacentHTML(
                    'beforeend',
                    '<div class="alert alert-warning text-center">No data available for the selected date range.</div>'
                );
                return;
            }
            
            // Remove any previous alert
            const existingAlert = document.querySelector('.chart-section .alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            document.getElementById('monthlyExpenseChart').style.display = 'block';
            
            // Check if chart instance already exists
            if (typeof monthlyChart !== 'undefined') {
                // Update existing chart with new data
                monthlyChart.data.labels = months;
                monthlyChart.data.datasets[0].data = totalIncome;
                monthlyChart.data.datasets[1].data = totalExpense;
                monthlyChart.data.datasets[2].data = netBalance;
                monthlyChart.update();
            } else {
                // Create a new chart instance
                const ctx = document.getElementById('monthlyExpenseChart').getContext('2d');
                monthlyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months, // Months
                        datasets: [
                            {
                                label: 'Total Income',
                                data: totalIncome,
                                borderColor: 'rgb(34, 139, 34)', // Green
                                backgroundColor: 'rgba(34, 139, 34, 0.2)', // Light green background
                                fill: true,
                                tension: 0.4, // Smooth corners
                            },
                            {
                                label: 'Total Expense',
                                data: totalExpense,
                                borderColor: 'rgb(220, 53, 69)', // Red
                                backgroundColor: 'rgba(220, 53, 69, 0.2)', // Light red background
                                fill: true,
                                tension: 0.4, // Smooth corners
                            },
                            {
                                label: 'Net Balance',
                                data: netBalance,
                                borderColor: 'rgb(0, 123, 255)', // Blue
                                backgroundColor: (context) =>
                                    context.raw < 0
                                ? 'rgba(255, 0, 0, 0.2)' // Red for negative
                                : 'rgba(0, 123, 255, 0.2)', // Blue for positive
                                fill: true,
                                tension: 0.4, // Smooth corners
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Month',
                                    font: {
                                        family: 'Poppins',
                                        weight: '600',
                                        size: 14,
                                    },
                                },
                                ticks: {
                                    font: {
                                        family: 'Poppins',
                                        weight: '400',
                                        size: 12,
                                    },
                                },
                            },
                            y: {
                                grid: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'Amount ($)',
                                    font: {
                                        family: 'Poppins',
                                        weight: '600',
                                        size: 14,
                                    },
                                },
                                ticks: {
                                    font: {
                                        family: 'Poppins',
                                        weight: '400',
                                        size: 12,
                                    },
                                    callback: function (value) {
                                        return `$${value.toLocaleString()}`;
                                    },
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    font: {
                                        family: 'Poppins',
                                        weight: '600',
                                        size: 12,
                                    },
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw;
                                        if (context.dataset.label === 'Net Balance') {
                                            return value >= 0
                                            ? `Net Balance: +$${value.toLocaleString()} (Surplus)`
                                            : `Net Balance: -$${Math.abs(value).toLocaleString()} (Deficit)`;
                                        }
                                        return `$${value.toLocaleString()}`;
                                    },
                                },
                                titleFont: {
                                    family: 'Poppins',
                                    weight: '600',
                                    size: 14,
                                },
                                bodyFont: {
                                    family: 'Poppins',
                                    weight: '400',
                                    size: 12,
                                },
                            },
                        },
                    },
                });
            }
        } else {
            console.error('Error fetching data:', data.message);
        }
    })
    .catch((error) => console.error('Error fetching data:', error));
}

// Initial Chart Load
fetchAndRenderChart('fetch_income_month.php'); // Load chart initially with default data

// Apply Filter Event Listener
document.getElementById('applyFilter').addEventListener('click', () => {
    const startMonth = document.getElementById('startMonth').value;
    const endMonth = document.getElementById('endMonth').value;
    
    // Construct the URL with filter parameters
    const filteredUrl = `fetch_income_month.php?startMonth=${startMonth}&endMonth=${endMonth}`;
    fetchAndRenderChart(filteredUrl); // Update the chart with filtered data
});
