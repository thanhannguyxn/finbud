fetch("financial_goal_api/fetch_finance_goals.php")
.then((response) => response.json())
.then((data) => {
    if (data.status === "success") {
        const financialGoals = data.data;
        
        // Clear old content before adding new ones
        d3.select("#financial-goals-chart").html("");
        
        // Check if no data is available
        if (financialGoals.length === 0) {
            d3.select("#financial-goals-chart")
            .append("div")
            .attr("class", "no-data")
            .style("text-align", "center")
            .style("color", "#666")
            .style("font-size", "16px")
            .style("margin", "20px 0")
            .text("No data available to display.");
            return;
        }
        
        financialGoals.forEach((goal) => {
            const percentage = Math.min(
                Math.max(goal.current_amount / goal.target_amount, 0),
                1
            );
            
            // Create a container for each goal
            const container = d3
            .select("#financial-goals-chart")
            .append("div")
            .attr("class", "goal-container");
            
            const width = 200;
            const height = 200;
            const radius = Math.min(width, height) / 2 - 10;
            
            // Create the SVG
            const svg = container
            .append("svg")
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", `translate(${width / 2}, ${height / 2})`);
            
            // Create defs for the gradient
            const defs = svg.append("defs");
            const gradientId = `gradient-${goal.goal_name.replace(/\s+/g, "-")}`;
            const gradient = defs
            .append("linearGradient")
            .attr("id", gradientId)
            .attr("x1", "0%")
            .attr("y1", "0%")
            .attr("x2", "100%")
            .attr("y2", "0%");
            
            // Light color at the start
            gradient
            .append("stop")
            .attr("offset", "0%")
            .attr("stop-color", "#B3E5FC");
            
            // Dark color at the end
            gradient
            .append("stop")
            .attr("offset", "100%")
            .attr("stop-color", "#0288D1");
            
            // Create the background circle
            const backgroundArc = d3
            .arc()
            .innerRadius(radius - 15)
            .outerRadius(radius)
            .startAngle(0)
            .endAngle(2 * Math.PI);
            
            // Create the foreground circle (completed portion)
            const foregroundArc = d3
            .arc()
            .innerRadius(radius - 15)
            .outerRadius(radius)
            .startAngle(0)
            .endAngle(2 * Math.PI * percentage);
            
            // Add the background part
            svg.append("path").attr("d", backgroundArc()).attr("fill", "#E0E0E0");
            
            // Add the completed portion with gradient
            svg
            .append("path")
            .attr("d", foregroundArc())
            .attr("fill", `url(#${gradientId})`);
            
            // Add percentage and goal name in the center of the circle
            svg
            .append("text")
            .attr("text-anchor", "middle")
            .attr("dy", "-0.5em")
            .style("font-size", "20px")
            .style("font-weight", "bold")
            .style("fill", "#0288D1")
            .text(`${(percentage * 100).toFixed(1)}%`);
            
            svg
            .append("text")
            .attr("text-anchor", "middle")
            .attr("dy", "1em")
            .style("font-size", "12px")
            .style("fill", "#666")
            .text(goal.goal_name);
            
            // Add goal details below the circle
            container.append("div").attr("class", "goal-details").html(`
                        <div><i class="fas fa-dollar-sign"></i> Target: $${goal.target_amount.toLocaleString()}</div>
                        <div><i class="fas fa-calendar-alt"></i> By ${
                goal.target_date
            }</div>
                    `);
        });
    } else {
        console.error("Error fetching financial goals:", data.message);
    }
})
.catch((error) => {
    console.error("Error fetching financial goals:", error);
    d3.select("#financial-goals-chart")
    .append("div")
    .attr("class", "error-message")
    .style("text-align", "center")
    .style("color", "#ff0000")
    .style("font-size", "16px")
    .text("An error occurred while fetching data.");
});
