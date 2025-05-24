import { toast as reactToast, ToastOptions, ToastContent, UpdateOptions } from "react-toastify"

type ToastProps = {
  title?: string
  description?: string
  variant?: "default" | "destructive"
}

export function useToast() {
  const toast = ({ title, description, variant = "default" }: ToastProps) => {
    const options: ToastOptions = {
      position: "top-right",
      autoClose: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      theme: "light",
      type: variant === "destructive" ? "error" : "success",
    }

    const content = (
      <div>
        {title && <div className="font-medium">{title}</div>}
        {description && <div className="text-sm text-gray-500">{description}</div>}
      </div>
    )

    return reactToast(content, options)
  }

  return { toast }
}
