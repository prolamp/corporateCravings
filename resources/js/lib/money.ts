/** Format amounts as “Rs. 1,250” for UI display. */
export function formatMoney(amount: number | string | null | undefined): string {
    const value = Number(amount ?? 0);

    return `Rs. ${value.toLocaleString('en-PK', {
        minimumFractionDigits: value % 1 === 0 ? 0 : 2,
        maximumFractionDigits: 2,
    })}`;
}

export function formatCount(value: number | null | undefined): string {
    return Number(value ?? 0).toLocaleString('en-PK');
}
