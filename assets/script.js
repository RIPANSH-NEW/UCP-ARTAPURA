  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');

  // Toggle sidebar on hamburger click
  menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    menuToggle.classList.toggle('hidden');
  });

  // Hide sidebar when clicking outside
  document.addEventListener('click', (e) => {
    const isClickInsideSidebar = sidebar.contains(e.target);
    const isClickMenuButton = menuToggle.contains(e.target);

    if (!isClickInsideSidebar && !isClickMenuButton && sidebar.classList.contains('active')) {
      sidebar.classList.remove('active');
      menuToggle.classList.remove('hidden');
    }
  });