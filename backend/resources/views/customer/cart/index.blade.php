@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm flex-shrink-0" style="width: 280px; z-index: 1040;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background-color: #f1f4f8;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 overflow-y-auto custom-scrollbar">
                <div class="container-fluid">
                    <div class="row g-4">
                        <!-- Cart Items Column -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-bottom p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0 text-dark">Your Shopping Cart</h5>
                                        <span class="badge bg-primary rounded-pill" id="cartTotalCount">{{ collect($cart)->sum('quantity') }}
                                            Items</span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    @if(count($cart) > 0)
                                        <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                                    <tr>
                                                        <th class="ps-4 py-3 border-0" style="width: 50px;">
                                                            <input type="checkbox" class="form-check-input shadow-sm curs-pointer" id="selectAll" checked>
                                                        </th>
                                                        <th class="py-3 border-0">Product</th>
                                                        <th class="py-3 border-0">Price</th>
                                                        <th class="py-3 border-0">Quantity</th>
                                                        <th class="py-3 border-0">Subtotal</th>
                                                        <th class="pe-4 py-3 border-0 text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $total = 0; @endphp
                                                    @foreach($cart as $id => $item)
                                                        @php $subtotal = $item['price'] * $item['quantity'];
                                                        $total += $subtotal; @endphp
                                                        <tr data-id="{{ $id }}" data-price="{{ $item['price'] }}">
                                                            <td class="ps-4 py-4 border-bottom-0">
                                                                <input type="checkbox" class="form-check-input shadow-sm curs-pointer item-checkbox" 
                                                                    value="{{ $id }}" 
                                                                    data-subtotal="{{ $subtotal }}" 
                                                                    checked>
                                                            </td>
                                                            <td class="py-4 border-bottom-0">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="rounded-3 overflow-hidden me-3 border"
                                                                        style="width: 60px; height: 60px;">
                                                                        <img src="{{ $item['image'] ?: asset('img/FABLAB-LOGO.png') }}"
                                                                            class="w-100 h-100 object-fit-cover"
                                                                            alt="{{ $item['name'] }}">
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="fw-bold text-dark mb-1">{{ $item['name'] }}</h6>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="small text-muted">{{ $item['sku'] }}</div>
                                                                            @if(isset($item['custom_design_id']))
                                                                                <span class="badge bg-soft-primary text-primary border border-primary-subtle tiny rounded-pill">Custom Design</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="py-4 border-bottom-0">
                                                                <span
                                                                    class="fw-medium">₱{{ number_format($item['price'], 2) }}</span>
                                                            </td>
                                                            <td class="py-4 border-bottom-0" style="min-width: 130px;">
                                                                <div class="input-group input-group-sm rounded-pill overflow-hidden border"
                                                                    style="width: 110px;">
                                                                    <button class="btn btn-light border-0 px-2 btn-decrease"
                                                                        type="button"><i class="bi bi-dash"></i></button>
                                                                    <input type="number"
                                                                        class="form-control border-0 text-center fw-bold bg-white cart-quantity shadow-none"
                                                                        value="{{ $item['quantity'] }}" min="1">
                                                                    <button class="btn btn-light border-0 px-2 btn-increase"
                                                                        type="button"><i class="bi bi-plus"></i></button>
                                                                </div>
                                                            </td>
                                                            <td class="py-4 border-bottom-0">
                                                                <span
                                                                    class="fw-bold text-primary cart-subtotal">₱{{ number_format($subtotal, 2) }}</span>
                                                            </td>
                                                            <td class="pe-4 py-4 border-bottom-0 text-end">
                                                                <button
                                                                    class="btn btn-light text-danger btn-sm rounded-circle border-0 shadow-sm btn-remove-item">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="bi bi-cart3 text-muted display-1 opacity-25"></i>
                                            </div>
                                            <h4 class="fw-bold text-dark">Your cart is empty</h4>
                                            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
                                            <a href="{{ route('customer.shop') }}"
                                                class="btn btn-primary rounded-pill px-5 fw-bold mt-3 shadow-sm">
                                                Go to Shop
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('customer.shop') }}" class="text-primary text-decoration-none fw-bold small">
                                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                            </a>
                        </div>

                        <!-- Summary Column -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h5 class="fw-bold mb-0 text-dark">Order Summary</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="fw-medium" id="summarySubtotal">₱{{ number_format($total ?? 0, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Estimated Tax</span>
                                        <span class="fw-medium">₱0.00</span>
                                    </div>

                                    <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-start">
                                        <i class="bi bi-info-circle-fill me-2 fs-5 mt-1"></i>
                                        <div class="small">
                                            <strong>Payment Notice:</strong><br>
                                            After placing your order, a receipt will be generated. Please present this receipt at the <strong>CSPC Cashier</strong> for payment.
                                        </div>
                                    </div>

                                    <hr class="my-4 opacity-50">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="fw-bold mb-0">Total</h5>
                                        <h4 class="fw-bold text-primary mb-0" id="summaryTotal">₱{{ number_format($total ?? 0, 2) }}</h4>
                                    </div>

                                    <form id="checkoutForm" action="{{ route('customer.cart.checkout') }}" method="POST">
                                        @csrf
                                        <div id="selectedItemsContainer"></div>
                                        <button type="submit" id="checkoutBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mb-3" {{ count($cart) == 0 ? 'disabled' : '' }}>
                                            Proceed to Checkout
                                        </button>
                                    </form>
                                    <p class="text-center text-muted small mb-0">
                                        <i class="bi bi-shield-check me-1"></i> Secure checkout powered by FABLAB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('customer.cart.components.delete-modal')
    @include('customer.cart.components.checkout-preview-modal')
    @include('customer.cart.components.approval-modal')

    <style>
        /* Hide spin buttons */
        .cart-quantity::-webkit-outer-spin-button,
        .cart-quantity::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .cart-quantity[type=number] {
            -moz-appearance: textfield;
        }

        .cart-quantity:focus {
            box-shadow: none;
        }

        .btn-light:hover {
            background-color: #f1f4f8;
        }
        
        .curs-pointer {
            cursor: pointer;
        }

        .custom-radio-box {
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .custom-radio-box:not(.disabled-payment):hover {
            border-color: #0d6efd;
            background-color: #f8fbff;
        }

        .custom-radio-box:has(.form-check-input:checked) {
            border-color: #0d6efd;
            background-color: #f0f7ff;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.05);
        }

        .disabled-payment {
            background-color: #f8f9fa;
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

    @push('scripts')
    <script>
        $(document).ready(function () {
            
            // --- Selection Logic ---
            function updateSummary() {
                let total = 0;
                let count = 0;
                
                $('.item-checkbox:checked').each(function() {
                    total += parseFloat($(this).data('subtotal'));
                    count++;
                });
                
                let formattedTotal = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                $('#summarySubtotal').text(formattedTotal);
                $('#summaryTotal').text(formattedTotal);
                
                // Disable checkout if no items selected
                if (count === 0) {
                    $('#checkoutBtn').prop('disabled', true);
                } else {
                    $('#checkoutBtn').prop('disabled', false);
                }
            }

            // Select All Toggle
            $('#selectAll').on('change', function() {
                $('.item-checkbox').prop('checked', $(this).is(':checked'));
                updateSummary();
            });

            // Individual Checkbox Click
            $('.item-checkbox').on('change', function() {
                if (!$(this).is(':checked')) {
                    $('#selectAll').prop('checked', false);
                } else if ($('.item-checkbox:checked').length === $('.item-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                }
                updateSummary();
            });

            // --- Preview & Checkout Logic ---
            $('#checkoutBtn').on('click', function(e) {
                e.preventDefault();
                
                const selectedItems = $('.item-checkbox:checked');
                if (selectedItems.length === 0) {
                     showToast('Please select at least one item.', 'error');
                     return;
                }

                // Populate Preview Modal
                const previewBody = $('#previewItemsBody');
                previewBody.empty();
                let checkTotal = 0;

                selectedItems.each(function() {
                    const row = $(this).closest('tr');
                    const name = row.find('h6').text();
                    const qty = row.find('.cart-quantity').val();
                    const subtotal = parseFloat($(this).data('subtotal'));
                    checkTotal += subtotal;

                    const previewRow = `
                        <tr>
                            <td><span class="fw-medium text-dark">${name}</span></td>
                            <td class="text-center text-muted">x${qty}</td>
                            <td class="text-end text-dark">₱${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    `;
                    previewBody.append(previewRow);
                });

                $('#previewTotal').text('₱' + checkTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                // Show Modal
                const previewModal = new bootstrap.Modal(document.getElementById('checkoutPreviewModal'));
                previewModal.show();
            });

            // Confirm Place Order
            $('#confirmPlaceOrderBtn').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).text('Processing...');

                // Collect selected items
                let selectedItems = [];
                $('.item-checkbox:checked').each(function() {
                    selectedItems.push($(this).val());
                });

                $.ajax({
                    url: "{{ route('customer.cart.checkout') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        selected_items: selectedItems
                    },
                    success: function(response) {
                        if (response.success) {
                            // Hide preview modal
                            const previewModalEl = document.getElementById('checkoutPreviewModal');
                            const previewModal = bootstrap.Modal.getInstance(previewModalEl);
                            if (previewModal) {
                                previewModal.hide();
                            }

                            // Show approval modal
                            const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));
                            approvalModal.show();
                        } else {
                             showToast(response.message || 'Checkout failed', 'error');
                             btn.prop('disabled', false).text('Confirm Order');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Confirm Order');
                        let msg = 'Checkout failed';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'error');
                    }
                });
            });


            // --- Existing Cart Logic ---

            // Store initial value on focus to allow revert on error
            $('.cart-quantity').on('focus', function() {
                $(this).data('previous-val', $(this).val());
            });

            // Input Change
            $('.cart-quantity').on('change', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                let qty = parseInt($(this).val());
                const previousQty = $(this).data('previous-val') || 1;
                
                if (isNaN(qty) || qty < 1) {
                    qty = 1;
                    // updateRowUI will set the value
                }
                
                updateCart(id, qty, row, previousQty);
                // Update previous value for next change (will be reverted if error occurs)
                $(this).data('previous-val', qty); 
            });

            // Increase Quantity
            $('.btn-increase').on('click', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const input = row.find('.cart-quantity');
                let qty = parseInt(input.val());
                if(isNaN(qty)) qty = 0;
                
                const previousQty = qty;
                const newQty = qty + 1;
                
                updateCart(id, newQty, row, previousQty);
            });

            // Decrease Quantity
            $('.btn-decrease').on('click', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const input = row.find('.cart-quantity');
                let qty = parseInt(input.val());
                if(isNaN(qty)) qty = 2; 
                
                if (qty > 1) {
                    const previousQty = qty;
                    const newQty = qty - 1;
                    updateCart(id, newQty, row, previousQty);
                }
            });

            // Remove Item
            let itemIdToDelete = null;
            $('.btn-remove-item').on('click', function () {
                itemIdToDelete = $(this).closest('tr').data('id');
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
                deleteModal.show();
            });

            $('#confirmDeleteBtn').on('click', function() {
                if (itemIdToDelete) {
                    removeItem(itemIdToDelete);
                }
            });

            function updateRowUI(row, qty) {
                const price = parseFloat(row.data('price'));
                const subtotal = price * qty;
                
                // Update Input Value
                row.find('.cart-quantity').val(qty);
                
                // Update Subtotal Text
                row.find('.cart-subtotal').text('₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                // Update Checkbox Data
                row.find('.item-checkbox').data('subtotal', subtotal);
                
                // Update Badge Count
                let totalQty = 0;
                $('.cart-quantity').each(function() {
                    totalQty += parseInt($(this).val()) || 0;
                });
                $('#cartTotalCount').text(totalQty + ' Items');

                // Recalculate Summary
                updateSummary();
            }

            function updateCart(id, qty, row, previousQty) {
                // Optimistic UI Update
                updateRowUI(row, qty);

                // Send Request
                $.ajax({
                    url: "{{ route('customer.cart.update') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: id,
                        quantity: qty
                    },
                    success: function (response) {
                        // Success - UI already updated
                        console.log('Cart updated successfully');
                    },
                    error: function (xhr) {
                        // Revert on error
                        showToast(xhr.responseJSON.message || 'Error updating cart', 'error');
                        
                        // Revert UI to previous state if available
                        if (previousQty !== undefined) {
                            updateRowUI(row, previousQty);
                            // Also revert the stored data for input change tracking
                            row.find('.cart-quantity').data('previous-val', previousQty);
                        } else {
                            // Fallback if no previous state (shouldn't happen with new logic)
                            location.reload();
                        }
                    }
                });
            }

            function removeItem(id) {
                $.ajax({
                    url: "{{ route('customer.cart.remove') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: id
                    },
                    success: function () {
                        location.reload();
                    },
                    error: function (xhr) {
                         showToast(xhr.responseJSON.message || 'Error removing item', 'error');
                    }
                });
            }
        });
    </script>
    @endpush
@endsection