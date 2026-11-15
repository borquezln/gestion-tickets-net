using System.Net;

namespace GestionTickets.Api.Tests;

public sealed class ApiStartupTests : IClassFixture<ApiWebApplicationFactory>
{
    private const string FrontendOrigin = "https://localhost:5173";
    private readonly ApiWebApplicationFactory _factory;

    public ApiStartupTests(ApiWebApplicationFactory factory)
    {
        _factory = factory;
    }

    [Fact]
    public async Task Application_starts_and_unknown_route_returns_not_found()
    {
        using var client = _factory.CreateClient();

        var response = await client.GetAsync("/api/v1/does-not-exist");

        Assert.Equal(HttpStatusCode.NotFound, response.StatusCode);
    }

    [Fact]
    public async Task Cors_preflight_allows_the_configured_frontend_origin()
    {
        using var client = _factory.CreateClient();
        using var request = CreatePreflightRequest(FrontendOrigin);

        var response = await client.SendAsync(request);

        Assert.Equal(HttpStatusCode.NoContent, response.StatusCode);
        Assert.Equal(FrontendOrigin, response.Headers.GetValues("Access-Control-Allow-Origin").Single());
        Assert.Equal("true", response.Headers.GetValues("Access-Control-Allow-Credentials").Single());
    }

    [Fact]
    public async Task Cors_preflight_does_not_allow_an_unknown_origin()
    {
        using var client = _factory.CreateClient();
        using var request = CreatePreflightRequest("https://evil.example");

        var response = await client.SendAsync(request);

        Assert.False(response.Headers.Contains("Access-Control-Allow-Origin"));
    }

    private static HttpRequestMessage CreatePreflightRequest(string origin)
    {
        var request = new HttpRequestMessage(HttpMethod.Options, "/api/v1/tickets");
        request.Headers.TryAddWithoutValidation("Origin", origin);
        request.Headers.TryAddWithoutValidation("Access-Control-Request-Method", "GET");
        return request;
    }
}
