# Jak spakować projekt do ZIP (bez pustego archiwum)

Masz dwa warianty w zależności od środowiska:

## Wymagania
Do przygotowania paczki we własnym środowisku potrzebujesz narzędzia `zip` (w
systemach Linux/macOS jest zwykle dostępne domyślnie; w Windows zainstalujesz je
razem z Git Bash lub możesz użyć PowerShell, patrz niżej).

## Windows (PowerShell)
1. Otwórz PowerShell w katalogu repozytorium.
2. Uruchom:
   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\package.ps1
   ```
3. Po zakończeniu pojawi się plik `fabryka_blysku.zip` (bez katalogu `.git`).

## Linux / macOS (bash)
1. W katalogu repozytorium wykonaj:
   ```bash
   ./package.sh
   ```
2. Powstanie plik `fabryka_blysku.zip` z pełną zawartością strony (bez `.git`).

Jeśli podczas rozpakowywania widzisz komunikat o pustym archiwum, upewnij się,
że skrypt był uruchomiony w katalogu projektu, pakowanie zakończyło się bez
komunikatów o błędach i że plik `.git` został wykluczony, a reszta plików była
w katalogu.

> Uwaga: katalog `assets/img/gallery/slides/` oraz inne ciężkie zasoby binarne
> nie są wersjonowane. Wstaw własne pliki (np. `.webp`, `.png`) lokalnie i
> uruchom pakowanie ponownie, by dodać je do archiwum. Repozytorium nie zawiera
> żadnych binariów ani favikon — należy je uzupełnić samodzielnie przed
> przygotowaniem paczki.
