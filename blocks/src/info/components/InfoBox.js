export const InfoBox = ({text}) => {
    return (
        <div className="info-box">
            <div className="info-icon-wrapper">
                <p className="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" width="52" height="52">
                        <circle
                            cx="26"
                            cy="26"
                            r="22"
                            fill="none"
                            stroke="#b22222"
                            stroke-width="4"
                        />

                        <circle
                            cx="26"
                            cy="14"
                            r="3"
                            fill="#b22222"
                        />

                        <path
                            d="M22 21h8v17h5v4H18v-4h4V25h-4z"
                            fill="#b22222"
                        />
                    </svg>
                </p>
            </div>

            <span className="info-text">
                { text }
            </span>
        </div>
    );
};
