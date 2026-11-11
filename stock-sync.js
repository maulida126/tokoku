(() => {
    const formatNumber = value => new Intl.NumberFormat('id-ID').format(value);

    async function updateStock() {
        const response = await fetch('stok_produk.php', {
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error(`Pembaruan stok gagal (HTTP ${response.status}).`);
        }

        const data = await response.json();
        const products = new Map(data.produk.map(product => [String(product.id), product]));

        document.querySelectorAll('.product-card[data-product-id]').forEach(card => {
            const button = card.querySelector('.buy-button');
            const product = products.get(button.dataset.productId);
            if (!product) {
                card.hidden = true;
                return;
            }

            card.hidden = false;
            button.dataset.productStock = product.stok;
            button.disabled = product.stok < 1;
            button.innerHTML = product.stok < 1
                ? '<i class="bi bi-x-circle"></i> Stok habis'
                : '<i class="bi bi-bag-plus"></i> Tambah ke keranjang';
            const stockLabel = card.querySelector('[data-stock-label]');
            stockLabel.textContent = product.stok > 0
                ? `Stok tersedia: ${formatNumber(product.stok)}`
                : 'Stok habis';
            card.classList.toggle('is-out-of-stock', product.stok < 1);
        });

        document.querySelectorAll('[data-cashier-product]').forEach(card => {
            const product = products.get(card.dataset.cashierProduct);
            if (!product) {
                card.hidden = true;
                return;
            }

            card.hidden = false;
            card.dataset.productStock = product.stok;
            const button = card.querySelector('[data-add-product]');
            button.disabled = product.stok < 1;
            const stockLabel = card.querySelector('[data-stock-label]');
            stockLabel.textContent = product.stok > 0
                ? `Stok ${formatNumber(product.stok)}`
                : 'Stok habis';
            stockLabel.classList.toggle('is-empty', product.stok < 1);
        });

        document.querySelectorAll('[data-stock-input]').forEach(input => {
            const product = products.get(input.dataset.stockInput);
            if (!product) {
                input.disabled = true;
                return;
            }

            const oldQuantity = Number(input.value) || 0;
            input.max = String(product.stok);
            input.disabled = product.stok < 1;
            if (oldQuantity > product.stok) {
                input.value = String(product.stok);
                const notice = document.getElementById('stockSyncNotice');
                if (notice) {
                    notice.textContent = `Jumlah ${product.nama} disesuaikan dengan stok terbaru (${formatNumber(product.stok)}).`;
                    notice.hidden = false;
                }
            }
            const stockLabel = document.querySelector(`[data-stock-label="${CSS.escape(product.id.toString())}"]`);
            if (stockLabel) {
                stockLabel.textContent = formatNumber(product.stok);
                stockLabel.classList.toggle('is-empty', product.stok < 1);
            }
        });

        const inventoryBadges = document.querySelectorAll('[data-stock-display]');
        let totalStok = 0;
        let stokHabis = 0;
        products.forEach(product => {
            totalStok += product.stok;
            if (product.stok < 1) stokHabis += 1;
        });
        inventoryBadges.forEach(badge => {
            const product = products.get(badge.dataset.stockDisplay);
            if (product) {
                badge.textContent = `${formatNumber(product.stok)} unit`;
                badge.classList.toggle('is-empty', product.stok < 1);
            }
        });
        const totalStockLabel = document.querySelector('[data-total-stock]');
        if (totalStockLabel) totalStockLabel.textContent = `${formatNumber(totalStok)} unit`;
        const emptyStockLabel = document.querySelector('[data-empty-stock]');
        if (emptyStockLabel) emptyStockLabel.textContent = `${formatNumber(stokHabis)} barang`;

        document.dispatchEvent(new CustomEvent('tokoku:stock-updated', { detail: { produk: data.produk } }));
    }

    updateStock().catch(error => console.error(error));
    window.setInterval(() => {
        updateStock().catch(error => console.error(error));
    }, 10000);
})();
