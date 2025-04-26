<style>
    #we-chat-qr-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        justify-content: center;
        display: none;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
        padding: 5rem 0;
    }

    .we-chat-qr-modal__content {
        position: relative;
        height: 70%;
        margin-top: 4rem;
    }

    .we-chat-qr-modal__content img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    #closeWeChatQrModal {
        position: absolute;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        top: 5px;
        right: 10px;
        border-radius: 50%;
        font-size: 1.5rem;
        border: 2px solid #4b6cb7;
        color: #4b6cb7;
        background-color: #fff;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    #closeWeChatQrModal:hover {
        transform: scale(1.1);
    }
</style>

<div id="we-chat-qr-modal">
    <div class="we-chat-qr-modal__content">
        <img src="<?= addMediaBaseUrl('resource/img/weChatQr.png') ?>" alt="We Chat Qr">
        <button id="closeWeChatQrModal">&times;</button>
    </div>
</div>