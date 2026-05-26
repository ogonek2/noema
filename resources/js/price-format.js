/** Matches App\Support\PriceFormat::uah() — regular space as thousands separator. */
export const formatUah = (amount) => {
    const value = Math.round(Number(amount) || 0);
    const formatted = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

    return `${formatted} ₴`;
};
