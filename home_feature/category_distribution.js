// Initialize the current date
let currentDate = new Date();
const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];

// Update the displayed month
function updateMonthDisplay() {
    const currentMonthElement = document.getElementById("current-month");
    const formattedMonth = `${
        monthNames[currentDate.getMonth()]
    } ${currentDate.getFullYear()}`;
    currentMonthElement.innerText = formattedMonth;
    renderPieChart(currentDate.toISOString().slice(0, 7)); // Update the chart with the current month
}

async function renderPieChart(
    selectedMonth = new Date().toISOString().slice(0, 7)
) {
    try {
        // Fetch data from API with the month parameter
        const response = await fetch(
            `expense_api/fetch_expense_category_percent.php?month=${selectedMonth}`
        );
        const result = await response.json();
        
        // Remove the old chart
        d3.select("#pie-chart").selectAll("*").remove();
        
        if (result.status === "success") {
            const data = result.data;
            if (data.length === 0) {
                d3.select("#pie-chart")
                .append("div")
                .attr("class", "no-data")
                .style("text-align", "center")
                .style("color", "#666")
                .style("font-size", "16px")
                .style("margin", "20px 0")
                .text("No data available to display.");
                return;
            }
            
            // Validate and transform data
            data.forEach((d) => {
                d.total_amount = parseFloat(d.total_amount) || 0; // Convert total_amount to a number
            });
            
            // Set up the SVG
            const width = 400;
            const height = 400;
            const radius = Math.min(width, height) / 2;
            
            const svg = d3
            .select("#pie-chart")
            .append("svg")
            .attr("width", width + 200) // Increase width to make room for the legend
            .attr("height", height)
            .append("g")
            .attr("transform", `translate(${width / 2}, ${height / 2})`);
            
            // Create a color scale
            const color = d3.scaleOrdinal(d3.schemeCategory10);
            
            // Create Pie and Arc
            const pie = d3
            .pie()
            .value((d) => d.total_amount)
            .sort(null);
            
            const arc = d3
            .arc()
            .innerRadius(0) // Full pie chart
            .outerRadius(radius);
            
            // Add tooltip
            const tooltip = d3
            .select("body")
            .append("div")
            .style("position", "absolute")
            .style("background", "#fff")
            .style("border", "1px solid #ccc")
            .style("padding", "5px 10px")
            .style("border-radius", "5px")
            .style("box-shadow", "0px 2px 5px rgba(0, 0, 0, 0.3)")
            .style("visibility", "hidden")
            .style("font-size", "12px");
            
            // Draw the chart
            svg
            .selectAll("path")
            .data(pie(data))
            .enter()
            .append("path")
            .attr("d", arc)
            .attr("fill", (d) => color(d.data.category_name))
            .attr("stroke", "#fff")
            .attr("stroke-width", "2px")
            .on("mouseover", (event, d) => {
                // Show tooltip
                tooltip
                .style("visibility", "visible")
                .html(
                    `<strong>${
                        d.data.category_name
                    }</strong>: $${d.data.total_amount.toFixed(2)}`
                );
            })
            .on("mousemove", (event) => {
                // Update tooltip position
                tooltip
                .style("top", `${event.pageY - 30}px`)
                .style("left", `${event.pageX + 10}px`);
            })
            .on("mouseout", () => {
                // Hide tooltip when hover ends
                tooltip.style("visibility", "hidden");
            });
            
            // Add labels inside the chart
            svg
            .selectAll("text")
            .data(pie(data))
            .enter()
            .append("text")
            .text((d) => `${d.data.percentage}%`)
            .attr("transform", (d) => `translate(${arc.centroid(d)})`)
            .style("text-anchor", "middle")
            .style("font-size", "12px");
            
            // Create legend
            const legend = d3
            .select("#pie-chart svg")
            .append("g")
            .attr("transform", `translate(${width}, 20)`); // Position the legend to the right of the chart
            
            legend
            .selectAll("rect")
            .data(data)
            .enter()
            .append("rect")
            .attr("x", 10)
            .attr("y", (d, i) => i * 20)
            .attr("width", 10)
            .attr("height", 10)
            .attr("fill", (d) => color(d.category_name));
            
            legend
            .selectAll("text")
            .data(data)
            .enter()
            .append("text")
            .attr("x", 25)
            .attr("y", (d, i) => i * 20 + 10)
            .text((d) => d.category_name)
            .style("font-size", "12px")
            .attr("alignment-baseline", "middle");
        } else {
            console.error("Failed to fetch data:", result.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

// Event when clicking the Previous Month button
document.getElementById("prev-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1); // Decrease by 1 month
    updateMonthDisplay();
});

// Event when clicking the Next Month button
document.getElementById("next-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1); // Increase by 1 month
    updateMonthDisplay();
});

// Call the function to display the month when the page loads
updateMonthDisplay();
