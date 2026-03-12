/**
 * Passenger Type Pricing Calculator
 * Auto-calculate booking price based on passenger types
 */

const PassengerPricing = {
    prices: {
        adult: 0,
        child: 0,
        infant: 0
    },

    /**
     * Initialize with prices from server
     */
    init: function(priceAdult, priceChild, priceInfant) {
        this.prices.adult = parseFloat(priceAdult) || 0;
        this.prices.child = parseFloat(priceChild) || 0;
        this.prices.infant = parseFloat(priceInfant) || 0;

        // Bind events
        this.bindEvents();
        
        // Initial calculation
        this.calculateTotal();
    },

    /**
     * Bind change events to passenger type selects and FOC checkboxes
     */
    bindEvents: function() {
        const self = this;
        
        // Use event delegation for dynamic rows
        $(document).on('change', '.passenger-type-select, .foc-checkbox', function() {
            const $row = $(this).closest('.companion-row, .guest-item');
            self.updateRowPrice($row);
            self.calculateTotal();
        });
    },

    /**
     * Update price display for a single row
     */
    updateRowPrice: function($row) {
        const type = $row.find('.passenger-type-select').val();
        const isFoc = $row.find('.foc-checkbox').is(':checked');
        const $priceDisplay = $row.find('.price-display');

        if (isFoc) {
            $priceDisplay.val('FOC').addClass('text-success fw-bold');
            $row.data('price', 0);
            return;
        }

        let price = 0;
        switch(type) {
            case 'adult':
                price = this.prices.adult;
                break;
            case 'child':
                price = this.prices.child;
                break;
            case 'infant':
                price = this.prices.infant;
                break;
        }

        $priceDisplay.val(this.formatPrice(price)).removeClass('text-success fw-bold');
        $row.data('price', price);
    },

    /**
     * Calculate total price for all passengers
     */
    calculateTotal: function() {
        let total = 0;
        const counts = {
            adult: 0,
            child: 0,
            infant: 0,
            foc: 0
        };

        // Count each type and sum prices
        $('.companion-row, .guest-item').each((index, row) => {
            const $row = $(row);
            const type = $row.find('.passenger-type-select').val();
            const isFoc = $row.find('.foc-checkbox').is(':checked');

            if (isFoc) {
                counts.foc++;
            } else {
                counts[type]++;
                const price = $row.data('price') || 0;
                total += price;
            }
        });

        // Update display
        $('#total_price').val(total);
        $('#total-price-display').text(this.formatPrice(total) + ' ₫');

        // Update breakdown if exists
        this.updateBreakdown(counts);
    },

    /**
     * Update price breakdown display
     */
    updateBreakdown: function(counts) {
        const breakdown = {
            adults: {
                count: counts.adult,
                price: this.prices.adult,
                subtotal: counts.adult * this.prices.adult
            },
            children: {
                count: counts.child,
                price: this.prices.child,
                subtotal: counts.child * this.prices.child
            },
            infants: {
                count: counts.infant,
                price: this.prices.infant,
                subtotal: counts.infant * this.prices.infant
            },
            foc: {
                count: counts.foc
            }
        };

        // Update breakdown display if element exists
        if ($('#price-breakdown').length) {
            let html = '<div class="small text-muted">';
            
            if (breakdown.adults.count > 0) {
                html += `<div>Người lớn: ${breakdown.adults.count} × ${this.formatPrice(breakdown.adults.price)} = ${this.formatPrice(breakdown.adults.subtotal)} ₫</div>`;
            }
            if (breakdown.children.count > 0) {
                html += `<div>Trẻ em: ${breakdown.children.count} × ${this.formatPrice(breakdown.children.price)} = ${this.formatPrice(breakdown.children.subtotal)} ₫</div>`;
            }
            if (breakdown.infants.count > 0) {
                html += `<div>Em bé: ${breakdown.infants.count} × ${this.formatPrice(breakdown.infants.price)} = ${this.formatPrice(breakdown.infants.subtotal)} ₫</div>`;
            }
            if (breakdown.foc.count > 0) {
                html += `<div>FOC: ${breakdown.foc.count} khách (miễn phí)</div>`;
            }
            
            html += '</div>';
            $('#price-breakdown').html(html);
        }
    },

    /**
     * Format price to Vietnamese format
     */
    formatPrice: function(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
};

// Export for use in other scripts
window.PassengerPricing = PassengerPricing;
