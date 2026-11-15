using GestionTickets.Api.Configuration;

namespace GestionTickets.Api.Tests;

public sealed class CorsSettingsTests
{
    [Fact]
    public void Validate_accepts_an_https_origin_without_path()
    {
        var settings = new CorsSettings
        {
            AllowedOrigins = ["https://localhost:5173"],
        };

        settings.Validate();
    }

    [Theory]
    [InlineData("")]
    [InlineData("http://localhost:5173")]
    [InlineData("https://localhost:5173/")]
    [InlineData("https://localhost:5173/api")]
    [InlineData("https://*.localhost:5173")]
    public void Validate_rejects_an_invalid_origin(string origin)
    {
        var settings = new CorsSettings
        {
            AllowedOrigins = [origin],
        };

        Assert.Throws<InvalidOperationException>(settings.Validate);
    }

    [Fact]
    public void Validate_rejects_an_empty_allowlist()
    {
        var settings = new CorsSettings();

        Assert.Throws<InvalidOperationException>(settings.Validate);
    }
}
