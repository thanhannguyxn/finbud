async function renderBarChart(type = "week") {
    try {
        // Call the API to get data
        const response = await fetch(
            `expense_api/fetch_expense_comparison.php?type=${type}`
        );
        const result = await response.json();
        
        if (result.status === "success") {
            const chartData = [
                { label: "Last", value: result.data.last || 0 },
                { label: "This", value: result.data.current || 0 },
            ];
            
            // Check if there is no data
            if (chartData.every((d) => d.value === 0)) {
                document.getElementById("bar-chart").innerHTML = `
                    <p class="text-center text-muted">No data available to display.</p>
                `;
                document.getElementById("total-spent").innerText = "$0.00";
                document.getElementById("comparison-period").innerText = `this ${type}`;
                return;
            }
            
            // Remove the old chart
            d3.select("#bar-chart").selectAll("*").remove();
            
            const width = 300;
            const height = 300;
            const margin = { top: 20, right: 20, bottom: 40, left: 40 };
            
            const svg = d3
            .select("#bar-chart")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", `translate(${margin.left}, ${margin.top})`);
            
            // Create scales for the X and Y axes
            const x = d3
            .scaleBand()
            .domain(chartData.map((d) => d.label))
            .range([0, width])
            .padding(0.3);
            
            const y = d3
            .scaleLinear()
            .domain([0, d3.max(chartData, (d) => d.value) || 10]) // Simulate a minimum of 10 if no data
            .nice()
            .range([height, 0]);
            
            // Draw the X axis
            svg
            .append("g")
            .attr("transform", `translate(0, ${height})`)
            .call(d3.axisBottom(x))
            .selectAll("text")
            .attr("text-anchor", "middle")
            .style("font-family", "Poppins, sans-serif");
            
            // Draw the Y axis
            svg
            .append("g")
            .call(d3.axisLeft(y).ticks(5))
            .selectAll("text")
            .style("font-family", "Poppins, sans-serif");
            
            // Draw the bars
            svg
            .selectAll(".bar")
            .data(chartData)
            .enter()
            .append("rect")
            .attr("x", (d) => x(d.label))
            .attr("y", (d) => y(d.value))
            .attr("width", x.bandwidth())
            .attr("height", (d) => height - y(d.value))
            .attr("fill", "#007bff")
            .attr("rx", 5);
            
            // Add values above the bars
            svg
            .selectAll(".text")
            .data(chartData)
            .enter()
            .append("text")
            .attr("x", (d) => x(d.label) + x.bandwidth() / 2)
            .attr("y", (d) => y(d.value) - 5)
            .attr("text-anchor", "middle")
            .text((d) => `$${d.value.toFixed(2)}`)
            .style("font-family", "Poppins, sans-serif")
            .style("font-size", "12px");
            
            // Update the header
            document.getElementById(
                "total-spent"
            ).innerText = `$${chartData[1].value.toFixed(2)}`;
            document.getElementById("comparison-period").innerText = `this ${type}`;
        } else {
            console.error("Failed to fetch data:", result.message);
            document.getElementById("bar-chart").innerHTML = `
                <p class="text-center text-danger">Error fetching data. Please try again later.</p>
            `;
        }
    } catch (error) {
        console.error("Error:", error);
        document.getElementById("bar-chart").innerHTML = `
            <p class="text-center text-danger">Error loading chart. Please try again later.</p>
        `;
    }
}

// Event to switch between week and month
document.getElementById("week-btn").addEventListener("click", () => {
    document.getElementById("week-btn").classList.add("active");
    document.getElementById("month-btn").classList.remove("active");
    renderBarChart("week");
});

document.getElementById("month-btn").addEventListener("click", () => {
    document.getElementById("month-btn").classList.add("active");
    document.getElementById("week-btn").classList.remove("active");
    renderBarChart("month");
});

// Display the initial chart
renderBarChart("week");
