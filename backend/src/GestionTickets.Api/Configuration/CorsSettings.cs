namespace GestionTickets.Api.Configuration;

/// <summary>
/// Allowlist of frontend origins allowed to call the API with credentials.
/// </summary>
public sealed class CorsSettings
{
    public const string SectionName = "Cors";
    public const string PolicyName = "Frontend";

    public string[] AllowedOrigins { get; set; } = [];

    /// <summary>
    /// Validates the configured origins before the application starts.
    /// </summary>
    /// <exception cref="InvalidOperationException">The allowlist is empty or contains an invalid origin.</exception>
    public void Validate()
    {
        if (AllowedOrigins is null || AllowedOrigins.Length == 0)
        {
            throw new InvalidOperationException(
                $"'{SectionName}:{nameof(AllowedOrigins)}' must contain at least one frontend origin.");
        }

        foreach (var origin in AllowedOrigins)
        {
            if (!IsValidOrigin(origin))
            {
                throw new InvalidOperationException(
                    $"'{SectionName}:{nameof(AllowedOrigins)}' contains the invalid origin '{origin}'. " +
                    "Each origin must be an absolute HTTPS origin without a trailing slash, path, query, fragment or wildcard.");
            }
        }
    }

    private static bool IsValidOrigin(string origin)
    {
        if (string.IsNullOrWhiteSpace(origin)
            || origin.Contains('*')
            || origin.EndsWith('/'))
        {
            return false;
        }

        return Uri.TryCreate(origin, UriKind.Absolute, out var uri)
            && uri.Scheme == Uri.UriSchemeHttps
            && string.IsNullOrEmpty(uri.UserInfo)
            && uri.AbsolutePath == "/"
            && string.IsNullOrEmpty(uri.Query)
            && string.IsNullOrEmpty(uri.Fragment);
    }
}
