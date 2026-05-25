<div id="cart-modal"
    class="cart-modal pointer-events-none fixed inset-0 z-[110] flex items-end justify-center opacity-0 transition-opacity duration-300 sm:items-center sm:p-6"
    data-cart-modal
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cart-modal-title">
    <div class="cart-modal-backdrop absolute inset-0 bg-black-brand/55 backdrop-blur-[3px]" data-cart-modal-close></div>

    <div class="cart-modal-panel relative flex max-h-[94dvh] w-full max-w-[560px] translate-y-6 flex-col overflow-hidden bg-white-brand text-black-brand shadow-2xl transition-transform duration-300 sm:max-w-[600px] sm:translate-y-4 sm:rounded-sm"
        data-cart-modal-panel>
        <div class="flex items-start justify-between gap-4 border-b border-black-brand/10 px-5 py-5 sm:px-6">
            <div class="min-w-0">
                <p class="text-[0.62rem] uppercase tracking-[0.2em] text-black-brand/45">Додати в кошик</p>
                <h2 id="cart-modal-title" class="mt-1 truncate text-[1.05rem] uppercase tracking-[0.05em] text-black-brand sm:text-[1.2rem]"
                    data-cart-modal-product-name>—</h2>
            </div>
            <button type="button"
                class="cart-modal-close shrink-0 p-2 text-black-brand/50 transition hover:text-black-brand"
                data-cart-modal-close
                aria-label="Закрити">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6" data-cart-modal-body>
            <div class="flex items-center justify-center py-16" data-cart-modal-loading>
                <div class="h-10 w-10 animate-pulse rounded-full border border-black-brand/15"></div>
            </div>
            <form class="hidden space-y-6" data-cart-modal-form novalidate>
                @csrf
                <input type="hidden" name="product_slug" data-cart-field="product_slug">

                <div class="flex gap-4">
                    <div class="h-24 w-20 shrink-0 overflow-hidden bg-black-brand/5">
                        <img src="" alt="" class="h-full w-full object-cover" data-cart-field="image">
                    </div>
                    <div class="min-w-0 pt-1">
                        <p class="text-[0.65rem] uppercase tracking-[0.16em] text-black-brand/45" data-cart-field="color"></p>
                        <p class="mt-2 text-[0.95rem] tracking-[0.08em] text-black-brand" data-cart-field="price"></p>
                        <a href="#" class="mt-2 inline-block text-[0.62rem] uppercase tracking-[0.14em] text-black-brand/45 underline-offset-2 hover:underline"
                            data-cart-field="url">Сторінка товару</a>
                    </div>
                </div>

                <div class="flex gap-1 border border-black-brand/10 p-1" role="tablist" aria-label="Режим додавання">
                    <button type="button" role="tab" aria-selected="true"
                        class="cart-mode-tab flex-1 border border-transparent px-3 py-2.5 text-[0.62rem] uppercase tracking-[0.14em] transition"
                        data-cart-mode="single">
                        Один товар
                    </button>
                    <button type="button" role="tab" aria-selected="false"
                        class="cart-mode-tab flex-1 border border-transparent px-3 py-2.5 text-[0.62rem] uppercase tracking-[0.14em] transition"
                        data-cart-mode="batch">
                        Набір
                    </button>
                </div>

                <div class="hidden space-y-4 border border-black-brand/10 bg-black-brand/[0.02] px-4 py-4" data-cart-batch-panel>
                    <div>
                        <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">
                            Кількість у наборі
                            <span class="normal-case tracking-normal text-black-brand/40">(коефіцієнт)</span>
                        </p>
                        <p class="mt-1 text-[0.72rem] leading-relaxed text-black-brand/50">
                            Наприклад, 30 костюмів різних розмірів — задайте число й налаштуйте кожен окремо або однаково.
                        </p>
                        <div class="mt-3 inline-flex items-center border border-black-brand/15">
                            <button type="button"
                                class="cart-qty-btn px-3 py-2.5 text-lg leading-none text-black-brand/60 transition hover:text-black-brand"
                                data-cart-batch-dec
                                aria-label="Зменшити набір">−</button>
                            <input type="number" value="3" min="2" max="50"
                                class="w-14 border-x border-black-brand/15 bg-white-brand py-2.5 text-center text-[0.82rem] tracking-[0.08em] text-black-brand [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                data-cart-batch-count>
                            <button type="button"
                                class="cart-qty-btn px-3 py-2.5 text-lg leading-none text-black-brand/60 transition hover:text-black-brand"
                                data-cart-batch-inc
                                aria-label="Збільшити набір">+</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2" role="group" aria-label="Тип набору">
                        <button type="button"
                            class="cart-batch-type min-w-0 flex-1 border px-3 py-2.5 text-[0.62rem] uppercase tracking-[0.12em] transition"
                            data-cart-batch-type="uniform">
                            Однакові для всіх
                        </button>
                        <button type="button"
                            class="cart-batch-type min-w-0 flex-1 border px-3 py-2.5 text-[0.62rem] uppercase tracking-[0.12em] transition"
                            data-cart-batch-type="individual">
                            Різні параметри
                        </button>
                    </div>
                </div>

                <div class="border-t border-black-brand/10 pt-5" data-cart-template-section>
                    <p class="mb-4 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55" data-cart-template-label>
                        Параметри
                    </p>

                    <div class="mb-5 hidden" data-cart-colors-wrap>
                        <p class="mb-3 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">Колір</p>
                        <div class="flex flex-wrap gap-2" data-cart-color-options role="group" aria-label="Колір"></div>
                    </div>

                    <div data-cart-field-wrap="size">
                        <p class="mb-3 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">
                            <span data-cart-size-label>Розмір</span> <span class="text-black-brand">*</span>
                        </p>
                        <div class="flex flex-wrap gap-2" data-cart-size-options></div>
                    </div>
                </div>

                <div data-cart-single-qty>
                    <p class="mb-3 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">Кількість</p>
                    <div class="inline-flex items-center border border-black-brand/15">
                        <button type="button"
                            class="cart-qty-btn px-3 py-2.5 text-lg leading-none text-black-brand/60 transition hover:text-black-brand"
                            data-cart-qty-dec
                            aria-label="Зменшити">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="99"
                            class="w-12 border-x border-black-brand/15 bg-white-brand py-2.5 text-center text-[0.82rem] tracking-[0.08em] text-black-brand [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            data-cart-field="quantity">
                        <button type="button"
                            class="cart-qty-btn px-3 py-2.5 text-lg leading-none text-black-brand/60 transition hover:text-black-brand"
                            data-cart-qty-inc
                            aria-label="Збільшити">+</button>
                    </div>
                </div>

                <div class="hidden space-y-3" data-cart-batch-entries-wrap>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">Позиції набору</p>
                        <button type="button"
                            class="border border-black-brand/15 px-3 py-1.5 text-[0.6rem] uppercase tracking-[0.12em] transition hover:border-black-brand"
                            data-cart-batch-apply-template>
                            Дублювати шаблон
                        </button>
                    </div>
                    <div class="cart-batch-entries max-h-[min(280px,40dvh)] space-y-2 overflow-y-auto pr-1" data-cart-batch-entries></div>
                </div>

                <div class="hidden space-y-5 border-t border-black-brand/10 pt-5" data-cart-customizations-wrap>
                    <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55" data-cart-customizations-label>Індивідуальні опції</p>
                    <div class="space-y-5" data-cart-customizations></div>
                </div>

                <div>
                    <label for="cart-modal-notes" class="mb-3 block text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">
                        <span data-cart-notes-label>Додаткові побажання</span>
                        <span class="normal-case tracking-normal text-black-brand/40">(необовʼязково)</span>
                    </label>
                    <textarea id="cart-modal-notes" name="notes" rows="3"
                        placeholder="Індивідуальні кишені, пошив, довжина рукава, вишивка…"
                        class="w-full resize-y border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.88rem] leading-relaxed text-black-brand placeholder:text-black-brand/30 focus:border-black-brand focus:outline-none"
                        data-cart-field="notes"></textarea>
                </div>

                <p class="hidden text-[0.72rem] text-red-700" data-cart-modal-error></p>
            </form>
        </div>

        <div class="border-t border-black-brand/10 px-5 py-4 sm:px-6">
            <div class="mb-3 flex items-baseline justify-between gap-3">
                <span class="text-[0.68rem] uppercase tracking-[0.16em] text-black-brand/45">Орієнтовно</span>
                <span class="text-[1.05rem] tracking-[0.08em] text-black-brand" data-cart-modal-total>0 ₴</span>
            </div>
            <button type="submit" form="" disabled
                class="cart-modal-submit w-full border border-black-brand bg-black-brand px-6 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition enabled:hover:bg-white-brand enabled:hover:text-black-brand disabled:cursor-not-allowed disabled:opacity-40"
                data-cart-modal-submit>
                Додати в кошик
            </button>
        </div>
    </div>
</div>
