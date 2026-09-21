export type TranslationStatus = 'queued' | 'processing' | 'completed' | 'failed' | 'cancelled'

export interface LanguageIssue {
  type: string
  original: string
  correction: string
  explanation: string
  severity: 'info' | 'warning' | 'error'
}

export interface NaturalVariant {
  expression: string
  pronunciation: string
  example: string
  example_translation: string
}

export interface TranslationRequestDto {
  id: string
  status: TranslationStatus
  translation: string | null
  source_corrected: string | null
  natural_usage: NaturalVariant | null
  issues: LanguageIssue[]
  error: string | null
}
