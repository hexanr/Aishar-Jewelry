<?php // admin/includes/admin_footer.php ?>
        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script>
function toggleAdminTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.getElementById('adminThemeIcon').className = 'fa ' + (next==='dark'?'fa-sun':'fa-moon');
    document.cookie = `theme=${next};path=/;max-age=31536000`;
}

function toggleSidebar() {
    const collapsed = document.documentElement.classList.toggle('sb-collapsed');
    try { localStorage.setItem('adminSidebar', collapsed ? 'closed' : 'open'); } catch (e) {}
}

function confirmDelete(msg) {
    return confirm(msg || 'هل أنتِ متأكدة من الحذف؟');
}

// Click on row → navigate
document.querySelectorAll('tr[data-href]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', e => {
        if (!e.target.closest('a,button,form,input,select')) {
            window.location.href = row.dataset.href;
        }
    });
});
</script>
</body>
</html>
