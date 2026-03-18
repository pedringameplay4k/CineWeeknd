    </div><!-- end main content -->
</div><!-- end flex wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
document.querySelectorAll('.toast').forEach(t => {
    setTimeout(() => { bootstrap.Toast.getOrCreateInstance(t).hide(); }, 4000);
});
</script>
</body>
</html>
