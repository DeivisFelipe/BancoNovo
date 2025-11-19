<template>
  <v-card elevation="8" class="rounded-lg">
    <v-card-title class="text-h4 text-center pa-6">
      LOGIN
    </v-card-title>
    <v-card-text class="pa-6">
      <v-form @submit.prevent="submit">
        <v-text-field label="Email" type="email" variant="outlined" prepend-inner-icon="mdi-email" class="mb-2" v-model="form.email" :error-messages="form.errors.email" />
        <v-text-field label="Senha" type="password" variant="outlined" prepend-inner-icon="mdi-lock" class="mb-2" v-model="form.password" :error-messages="form.errors.password" />
        <v-btn color="primary" block size="large" class="mt-4 mb-4" type="submit" :loading="form.processing">
          Entrar
        </v-btn>

        <div class="text-center my-4">
          <div class="d-flex align-center">
            <v-divider></v-divider>
            <span class="mx-3 text-caption text-medium-emphasis">ou</span>
            <v-divider></v-divider>
          </div>
        </div>

        <div class="text-center">
          <span class="text-body-2">Não tem uma conta? </span>
          <a href="/register" class="text-primary text-decoration-none font-weight-medium cursor-pointer" @click.prevent="$inertia.visit('/register')">
            Criar conta
          </a>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post("/login", {
    onFinish: () => form.reset("password"),
    onError: (errors) => {
      if (errors.email) {
        Swal.fire({
          icon: "error",
          title: "Erro ao fazer login",
          text: errors.email,
          confirmButtonColor: "#1976D2",
        });
      }
    },
  });
};
</script>
