<script setup>
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    value: { type: String, required: true },
    name: { type: String, default: 'clb' },
});

const canvas = ref(null);

async function draw() {
    if (!canvas.value) {
        return;
    }

    await QRCode.toCanvas(canvas.value, props.value, { width: 192, margin: 1 });
}

function fileName() {
    const slug = props.name
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

    return `ma-qr-${slug || 'clb'}.png`;
}

async function download() {
    const exportCanvas = document.createElement('canvas');
    await QRCode.toCanvas(exportCanvas, props.value, { width: 768, margin: 2 });

    const link = document.createElement('a');
    link.href = exportCanvas.toDataURL('image/png');
    link.download = fileName();
    link.click();
}

onMounted(draw);
watch(() => props.value, draw);
</script>

<template>
    <div>
        <canvas ref="canvas" class="mx-auto rounded-2xl bg-white" />
        <button type="button" class="mt-4 flex min-h-11 w-full items-center justify-center rounded-2xl bg-stone-100 font-semibold" @click="download">Tải về máy</button>
    </div>
</template>
