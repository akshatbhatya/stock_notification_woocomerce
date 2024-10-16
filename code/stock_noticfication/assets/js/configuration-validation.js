let table = new DataTable('.myTable', {
  paging: true,
  searching: true,
  ordering: true,
  info: true,
  pageLength: 5,
  lengthMenu: [5, 10, 25, 50, 100],
  order: [[0, "asc"]],
  columnDefs: [
      {      
          targets: 0,
          orderable: false
      }
  ],
  responsive: true,
  language: {
      search: "Search:",
      lengthMenu: "Show _MENU_ entries", 
      info: "Showing _START_ to _END_ of _TOTAL_ entries", 
      paginate: {
          first: "First",
          last: "Last",
          next: "Next",
          previous: "Previous"
      }
  }
});
