const incomeApp = Vue.createApp({
  data() {
    return {
      incomes: [], // List of incomes
      categories: [], // List of income categories
      filters: {
        minAmount: "",
        maxAmount: "",
        category: "",
        startDate: "",
        endDate: "",
      },
      currentPage: 1, // Current page
      itemsPerPage: 5, // Number of records per page
    };
  },
  computed: {
    hasFilters() {
      // Check if at least one filter is applied
      return (
        this.filters.minAmount ||
        this.filters.maxAmount ||
        this.filters.category ||
        this.filters.startDate ||
        this.filters.endDate
      );
    },
    filteredIncomes() {
      // If no filters are applied, return the full list
      if (!this.hasFilters) {
        return this.incomes;
      }

      // Filter the income records based on the applied filters
      return this.incomes.filter((income) => {
        const minAmount = this.filters.minAmount || 0;
        const maxAmount = this.filters.maxAmount || Number.MAX_VALUE;
        const category = this.filters.category;
        const startDate = this.filters.startDate;
        const endDate = this.filters.endDate;

        const matchesAmount =
          income.amount >= minAmount && income.amount <= maxAmount;
        const matchesCategory =
          !category || income.income_category_id === parseInt(category);
        const matchesStartDate =
          !startDate || new Date(income.income_date) >= new Date(startDate);
        const matchesEndDate =
          !endDate || new Date(income.income_date) <= new Date(endDate);

        return (
          matchesAmount && matchesCategory && matchesStartDate && matchesEndDate
        );
      });
    },
    paginatedIncomes() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredIncomes.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredIncomes.length / this.itemsPerPage);
    },
  },
  methods: {
    async fetchIncomes() {
      try {
        const response = await fetch("income_api/fetch_incomes.php");
        const data = await response.json();
        this.incomes = data.incomes || [];
      } catch (error) {
        console.error("Error fetching incomes:", error);
      }
    },
    async fetchCategories() {
      try {
        const response = await fetch("income_api/fetch_income_categories.php");
        const data = await response.json();
        this.categories = data.categories || [];
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    applyFilters() {
      this.currentPage = 1; // Reset to the first page when filters are applied
    },
  },
  async mounted() {
    await this.fetchIncomes();
    await this.fetchCategories();
  },
});

incomeApp.mount("#incomeApp");
