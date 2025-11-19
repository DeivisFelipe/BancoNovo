<template>
  <v-app>
    <canvas ref="canvas" class="particles-canvas"></canvas>
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useFlashMessages } from "../composables/useFlashMessages";

useFlashMessages();

const canvas = ref(null);
let animationId = null;
let particles = [];

class Particle {
  constructor(canvasWidth, canvasHeight) {
    this.x = Math.random() * canvasWidth;
    this.y = Math.random() * canvasHeight;
    this.size = Math.random() * 3 + 1;
    this.speedX = Math.random() * 1 - 0.5;
    this.speedY = Math.random() * 1 - 0.5;
    this.opacity = Math.random() * 0.5 + 0.2;
  }

  update(canvasWidth, canvasHeight) {
    this.x += this.speedX;
    this.y += this.speedY;

    if (this.x > canvasWidth) this.x = 0;
    if (this.x < 0) this.x = canvasWidth;
    if (this.y > canvasHeight) this.y = 0;
    if (this.y < 0) this.y = canvasHeight;
  }

  draw(ctx) {
    ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

onMounted(() => {
  const ctx = canvas.value.getContext("2d");
  canvas.value.width = window.innerWidth;
  canvas.value.height = window.innerHeight;

  for (let i = 0; i < 100; i++) {
    particles.push(new Particle(canvas.value.width, canvas.value.height));
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);

    particles.forEach((particle) => {
      particle.update(canvas.value.width, canvas.value.height);
      particle.draw(ctx);
    });

    particles.forEach((p1, i) => {
      particles.slice(i + 1).forEach((p2) => {
        const dx = p1.x - p2.x;
        const dy = p1.y - p2.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance < 100) {
          ctx.strokeStyle = `rgba(255, 255, 255, ${
            0.1 * (1 - distance / 100)
          })`;
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(p1.x, p1.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.stroke();
        }
      });
    });

    animationId = requestAnimationFrame(animate);
  }

  animate();

  const handleResize = () => {
    canvas.value.width = window.innerWidth;
    canvas.value.height = window.innerHeight;
  };

  window.addEventListener("resize", handleResize);

  onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
    if (animationId) {
      cancelAnimationFrame(animationId);
    }
  });
});
</script>

<style scoped>
.particles-canvas {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  z-index: 0;
}

.v-main {
  position: relative;
  z-index: 1;
}
</style>
