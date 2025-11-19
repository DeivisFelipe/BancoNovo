import { usePage } from "@inertiajs/vue3";
import { watch } from "vue";
import Swal from "sweetalert2";

export function useFlashMessages() {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;

            if (flash.success) {
                Swal.fire({
                    icon: "success",
                    title: flash.success.title || "Sucesso!",
                    text: flash.success.message || "",
                    confirmButtonColor: "#1976D2",
                    timer: 3000,
                    timerProgressBar: true,
                });
            }

            if (flash.error) {
                Swal.fire({
                    icon: "error",
                    title: flash.error.title || "Erro!",
                    text: flash.error.message || "Algo deu errado.",
                    confirmButtonColor: "#1976D2",
                });
            }

            if (flash.warning) {
                Swal.fire({
                    icon: "warning",
                    title: flash.warning.title || "Atenção!",
                    text: flash.warning.message || "",
                    confirmButtonColor: "#1976D2",
                });
            }

            if (flash.info) {
                Swal.fire({
                    icon: "info",
                    title: flash.info.title || "Informação",
                    text: flash.info.message || "",
                    confirmButtonColor: "#1976D2",
                });
            }
        },
        { deep: true, immediate: true }
    );
}
