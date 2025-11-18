<template>
  <v-card elevation="8" class="rounded-lg">
    <v-card-title class="text-h5 text-center pa-6 bg-primary">
      <span class="text-white">Login</span>
    </v-card-title>
    <v-card-text class="pa-6">
      <v-form>
        <v-text-field label="Email" type="email" variant="outlined" prepend-inner-icon="mdi-email" class="mb-2" />
        <v-text-field label="Senha" type="password" variant="outlined" prepend-inner-icon="mdi-lock" class="mb-2" />
        <v-btn color="primary" block size="large" class="mt-4 mb-4">
          Entrar
        </v-btn>
        <v-divider class="my-4"></v-divider>
        <div class="text-center">
          <p class="text-body-2 mb-2">Não tem uma conta?</p>
          <v-btn variant="outlined" color="primary" block @click="$inertia.visit('/register')">
            Criar conta
          </v-btn>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
</script>
