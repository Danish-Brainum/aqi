export function injectLoaderCss() {
    const style = document.createElement("style");
    style.innerHTML = `
        .loader {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #16a34a;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}
